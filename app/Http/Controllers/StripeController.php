<?php

namespace App\Http\Controllers;

use App\Mail\PedidoConfirmado;
use App\Models\Notificacion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\RusticoinTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeController extends Controller
{
    /**
     * Crea un PaymentIntent en Stripe y devuelve el client_secret al frontend.
     * El pedido aún NO se crea aquí — se crea solo cuando Stripe confirma el pago.
     */
    public function createIntent(Request $request)
    {
        $validated = $request->validate([
            'items'                 => 'required|array|min:1',
            'items.*.id'            => 'required|integer|exists:productos,id',
            'items.*.cantidad'      => 'required|integer|min:1|max:99',
            'direccion_envio'       => 'required|string|max:500',
            'telefono_contacto'     => 'required|string|max:20',
            'notas'                 => 'nullable|string|max:1000',
        ]);

        // Calcular total real desde BD (nunca confiar en el cliente)
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $producto = Producto::findOrFail($item['id']);

            if (!$producto->disponible) {
                return response()->json(['error' => "El producto \"{$producto->nombre}\" ya no está disponible."], 422);
            }
            if ($producto->stock < $item['cantidad']) {
                return response()->json(['error' => "Stock insuficiente para \"{$producto->nombre}\". Quedan {$producto->stock} unidades."], 422);
            }

            $precioReal = (float) ($producto->precio_oferta ?? $producto->precio);
            $subtotal  += $precioReal * $item['cantidad'];
        }

        $gastosEnvio = $subtotal >= 50 ? 0.00 : 2.50;
        $total       = $subtotal + $gastosEnvio;

        // Stripe trabaja en céntimos (enteros)
        $amountCents = (int) round($total * 100);

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $intent = PaymentIntent::create([
                'amount'   => $amountCents,
                'currency' => 'eur',
                'metadata' => [
                    'user_id'           => auth()->id(),
                    'direccion_envio'   => $validated['direccion_envio'],
                    'telefono_contacto' => $validated['telefono_contacto'],
                    'notas'             => $validated['notas'] ?? '',
                    'items_json'        => json_encode($validated['items']),
                ],
                'automatic_payment_methods' => ['enabled' => true],
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('[Stripe createIntent] ' . $e->getMessage());
            return response()->json(['error' => 'Error al conectar con Stripe: ' . $e->getMessage()], 502);
        }

        return response()->json([
            'client_secret' => $intent->client_secret,
            'amount'        => $total,
        ]);
    }

    /**
     * Llamado por el frontend tras confirmPayment exitoso.
     * Crea el pedido si el webhook aún no lo ha creado (idempotente).
     */
    public function confirm(Request $request)
    {
        $request->validate(['payment_intent_id' => 'required|string']);

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $intent = PaymentIntent::retrieve($request->payment_intent_id);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['error' => 'No se pudo verificar el pago.'], 502);
        }

        if ($intent->status !== 'succeeded') {
            return response()->json(['error' => 'El pago no está confirmado.'], 422);
        }

        // Crear pedido si no existe aún (el webhook puede llegar después)
        $this->crearPedidoDesdeIntent($intent);

        return response()->json(['ok' => true]);
    }

    /**
     * Webhook de Stripe: recibe payment_intent.succeeded y crea el pedido.
     * Esta ruta está excluida del middleware CSRF.
     */
    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature inválida', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $intent = $event->data->object;
            $this->crearPedidoDesdeIntent($intent);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Crea el pedido en BD a partir de los metadatos del PaymentIntent confirmado.
     */
    private function crearPedidoDesdeIntent(object $intent): void
    {
        // Evitar pedidos duplicados si el webhook llega más de una vez
        if (Pedido::where('stripe_payment_intent_id', $intent->id)->exists()) {
            Log::info('[Stripe] Pedido ya existe para intent ' . $intent->id);
            return;
        }

        $meta   = $intent->metadata;
        $userId = (int) $meta->user_id;
        $items  = json_decode($meta->items_json, true);

        Log::info('[Stripe] Creando pedido para intent ' . $intent->id . ' user_id=' . $userId . ' items=' . count($items));

        try {
        $pedido = DB::transaction(function () use ($intent, $meta, $userId, $items) {
            $subtotal = 0;
            $itemsConPrecio = [];

            foreach ($items as $item) {
                $producto   = Producto::lockForUpdate()->find($item['id']);
                $precioReal = (float) ($producto->precio_oferta ?? $producto->precio);
                $subtotal  += $precioReal * $item['cantidad'];

                $itemsConPrecio[] = array_merge($item, [
                    'precio_real'    => $precioReal,
                    'producto_obj'   => $producto,
                ]);
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
                'stripe_payment_intent_id' => $intent->id,
            ]);

            foreach ($itemsConPrecio as $item) {
                $producto = $item['producto_obj'];

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
            Log::error('[Stripe] Error creando pedido para intent ' . $intent->id . ': ' . $e->getMessage());
            return;
        }

        Log::info('[Stripe] Pedido creado: ' . $pedido->numero_pedido);

        // Email fuera de la transacción para no bloquearla
        $pedido->load(['items.tienda.user', 'user']);
        try {
            Mail::to($pedido->user->email)->send(new PedidoConfirmado($pedido));
        } catch (\Throwable $e) {
            Log::error('[PedidoConfirmado email] ' . $e->getMessage(), ['pedido_id' => $pedido->id]);
        }

        // Notificar owners
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
    }
}
