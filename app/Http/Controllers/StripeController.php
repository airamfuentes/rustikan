<?php

namespace App\Http\Controllers;

use App\Mail\PedidoConfirmado;
use App\Models\Notificacion;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeController extends Controller
{
    /**
     * Crea una Stripe Checkout Session y redirige al usuario a la página de pago de Stripe.
     */
    public function createSession(Request $request)
    {
        $validated = $request->validate([
            'items'                 => 'required|array|min:1',
            'items.*.id'            => 'required|integer|exists:productos,id',
            'items.*.cantidad'      => 'required|integer|min:1|max:99',
            'direccion_envio'       => 'required|string|max:500',
            'telefono_contacto'     => 'required|string|max:20',
            'notas'                 => 'nullable|string|max:1000',
        ]);

        // Calcular total y construir line_items desde BD
        $lineItems = [];
        foreach ($validated['items'] as $item) {
            $producto = Producto::findOrFail($item['id']);

            if (!$producto->disponible) {
                return back()->withErrors(['stock' => "El producto \"{$producto->nombre}\" ya no está disponible."]);
            }
            if ($producto->stock < $item['cantidad']) {
                return back()->withErrors(['stock' => "Stock insuficiente para \"{$producto->nombre}\". Quedan {$producto->stock} unidades."]);
            }

            $precioReal = (float) ($producto->precio_oferta ?? $producto->precio);

            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'eur',
                    'unit_amount'  => (int) round($precioReal * 100),
                    'product_data' => [
                        'name'   => $producto->nombre,
                        'images' => $producto->imagen ? [asset('storage/' . $producto->imagen)] : [],
                    ],
                ],
                'quantity' => $item['cantidad'],
            ];
        }

        // Gastos de envío
        $subtotal    = collect($validated['items'])->sum(function ($item) {
            $p = Producto::find($item['id']);
            return (float) ($p->precio_oferta ?? $p->precio) * $item['cantidad'];
        });
        $gastosEnvio = $subtotal >= 50 ? 0 : 2.50;
        if ($gastosEnvio > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'eur',
                    'unit_amount'  => (int) round($gastosEnvio * 100),
                    'product_data' => ['name' => 'Gastos de envío'],
                ],
                'quantity' => 1,
            ];
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items'           => $lineItems,
                'mode'                 => 'payment',
                'locale'               => 'es',
                'success_url'          => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'           => route('carrito'),
                'metadata'             => [
                    'user_id'           => auth()->id(),
                    'direccion_envio'   => $validated['direccion_envio'],
                    'telefono_contacto' => $validated['telefono_contacto'],
                    'notas'             => $validated['notas'] ?? '',
                    'items_json'        => json_encode($validated['items']),
                ],
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('[Stripe createSession] ' . $e->getMessage());
            return back()->withErrors(['stripe' => 'Error al conectar con Stripe. Inténtalo de nuevo.']);
        }

        return redirect($session->url);
    }

    /**
     * Página de éxito: Stripe redirige aquí tras el pago.
     * No requiere auth — el user_id está en los metadatos de la sesión.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return redirect()->route('carrito');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = StripeSession::retrieve($sessionId);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('[Stripe success] retrieve falló: ' . $e->getMessage());
            return redirect()->route('pedidos.index')->with('error', 'No se pudo verificar el pago.');
        }

        Log::info('[Stripe success] session=' . $sessionId . ' payment_status=' . $session->payment_status);

        if ($session->payment_status !== 'paid') {
            return redirect()->route('carrito')->with('error', 'El pago no se completó.');
        }

        $pedido = $this->crearPedidoDesdeSession($session);

        // Loguear al usuario si su sesión sigue activa (puede haberse perdido en redirect externo)
        if (!auth()->check() && $pedido) {
            auth()->loginUsingId($pedido->user_id);
        }

        return inertia('CheckoutSuccess', [
            'pedido' => $pedido ? [
                'numero_pedido' => $pedido->numero_pedido,
                'total'         => $pedido->total,
            ] : null,
        ]);
    }

    /**
     * Webhook de Stripe: crea el pedido como respaldo si el usuario cerró el navegador.
     */
    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('[Stripe webhook] Firma inválida: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            if ($session->payment_status === 'paid') {
                // Expand payment_intent si es necesario
                Stripe::setApiKey(config('services.stripe.secret'));
                $session = StripeSession::retrieve([
                    'id'     => $session->id,
                    'expand' => ['payment_intent'],
                ]);
                $this->crearPedidoDesdeSession($session);
            }
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Crea el pedido a partir de una Checkout Session. Idempotente.
     */
    private function crearPedidoDesdeSession(object $session): ?Pedido
    {
        $stripeSessionId = $session->id;

        // Evitar duplicados
        if (Pedido::where('stripe_payment_intent_id', $stripeSessionId)->exists()) {
            return Pedido::where('stripe_payment_intent_id', $stripeSessionId)->first();
        }

        $meta   = $session->metadata;
        $userId = (int) $meta->user_id;
        $items  = json_decode($meta->items_json, true);

        Log::info('[Stripe] Creando pedido session=' . $stripeSessionId . ' user=' . $userId);

        try {
            $pedido = DB::transaction(function () use ($session, $meta, $userId, $items, $stripeSessionId) {
                $subtotal       = 0;
                $itemsConPrecio = [];

                foreach ($items as $item) {
                    $producto   = Producto::lockForUpdate()->findOrFail($item['id']);
                    $precioReal = (float) ($producto->precio_oferta ?? $producto->precio);
                    $subtotal  += $precioReal * $item['cantidad'];

                    $itemsConPrecio[] = [
                        'id'          => $item['id'],
                        'cantidad'    => $item['cantidad'],
                        'precio_real' => $precioReal,
                        'producto'    => $producto,
                    ];
                }

                $gastosEnvio = $subtotal >= 50 ? 0.00 : 2.50;
                $total       = $subtotal + $gastosEnvio;

                $pedido = Pedido::create([
                    'user_id'                  => $userId,
                    'numero_pedido'            => Pedido::generateOrderNumber(),
                    'estado'                   => 'confirmado',
                    'subtotal'                 => $subtotal,
                    'gastos_envio'             => $gastosEnvio,
                    'total'                    => $total,
                    'direccion_envio'          => $meta->direccion_envio,
                    'telefono_contacto'        => $meta->telefono_contacto,
                    'notas'                    => $meta->notas ?? null,
                    'metodo_pago'              => 'tarjeta',
                    'stripe_payment_intent_id' => $stripeSessionId,
                ]);

                foreach ($itemsConPrecio as $item) {
                    $producto = $item['producto'];
                    $pedido->items()->create([
                        'producto_id'     => $item['id'],
                        'tienda_id'       => $producto->tienda_id,
                        'producto_nombre' => $producto->nombre,
                        'tienda_nombre'   => $producto->tienda->nombre ?? '',
                        'producto_imagen' => $producto->imagen ?? null,
                        'cantidad'        => $item['cantidad'],
                        'precio_unitario' => $item['precio_real'],
                        'subtotal'        => $item['precio_real'] * $item['cantidad'],
                    ]);
                    $producto->decrement('stock', $item['cantidad']);
                }

                return $pedido;
            });
        } catch (\Throwable $e) {
            Log::error('[Stripe] Error creando pedido: ' . $e->getMessage());
            return null;
        }

        Log::info('[Stripe] Pedido creado: ' . $pedido->numero_pedido);

        // Email y notificaciones fuera de la transacción
        $pedido->load(['items.tienda.user', 'user']);

        try {
            Mail::to($pedido->user->email)->send(new PedidoConfirmado($pedido));
        } catch (\Throwable $e) {
            Log::error('[Stripe] Email falló: ' . $e->getMessage());
        }

        $notificados = [];
        foreach ($pedido->items as $item) {
            $ownerId = $item->tienda?->user_id;
            if ($ownerId && !in_array($ownerId, $notificados)) {
                $notificados[] = $ownerId;
                Notificacion::enviar(
                    $ownerId, 'nuevo_pedido', 'Nuevo pedido recibido',
                    "Has recibido el pedido #{$pedido->numero_pedido} por " . number_format($pedido->total, 2) . '€',
                    route('owner.panel'), 'cart', 'green'
                );
            }
        }

        Notificacion::enviarAdmins(
            'nuevo_pedido', 'Nuevo pedido en la plataforma',
            "Pedido #{$pedido->numero_pedido} por " . number_format($pedido->total, 2) . '€',
            route('admin.pedidos.index'), 'cart', 'primary'
        );

        return $pedido;
    }
}
