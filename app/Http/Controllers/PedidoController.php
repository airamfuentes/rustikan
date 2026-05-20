<?php

namespace App\Http\Controllers;

use App\Mail\PedidoConfirmado;
use App\Models\Notificacion;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\Resena;
use App\Models\RusticoinTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class PedidoController extends Controller
{
    /**
     * Crear un nuevo pedido desde el carrito del usuario.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items'                    => 'required|array|min:1',
            'items.*.id'               => 'required|integer|exists:productos,id',
            'items.*.cantidad'         => 'required|integer|min:1|max:99',
            'items.*.precio'           => 'required|numeric|min:0',
            'items.*.nombre'           => 'required|string|max:255',
            'items.*.tienda_id'        => 'required|integer|exists:tiendas,id',
            'items.*.tienda_nombre'    => 'required|string|max:255',
            'items.*.imagen'           => 'nullable|string|max:500',
            'items.*.unidad'           => 'nullable|string|max:50',
            'direccion_envio'          => 'required|string|max:500',
            'telefono_contacto'        => 'required|string|max:20',
            'notas'                    => 'nullable|string|max:1000',
            'metodo_pago'              => 'nullable|in:tarjeta,rusticoin',
        ]);

        try {
            $metodoPago = $validated['metodo_pago'] ?? 'tarjeta';

            $pedido = DB::transaction(function () use ($validated, $metodoPago) {
                $user = auth()->user();

                // Verificar stock para cada producto (con bloqueo optimista)
                foreach ($validated['items'] as $item) {
                    $producto = Producto::lockForUpdate()->find($item['id']);

                    if (!$producto || !$producto->disponible) {
                        throw new \Exception("El producto \"{$item['nombre']}\" ya no está disponible.");
                    }

                    if ($producto->stock < $item['cantidad']) {
                        throw new \Exception("Stock insuficiente para \"{$item['nombre']}\". Quedan {$producto->stock} unidades.");
                    }
                }

                // Calcular totales usando los precios reales de la BD (no del cliente)
                $subtotal = 0;
                $itemsConPrecio = [];

                foreach ($validated['items'] as $item) {
                    $producto = Producto::find($item['id']);
                    $precioReal = $producto->precio_oferta ?? $producto->precio;
                    $subtotal += $precioReal * $item['cantidad'];

                    $itemsConPrecio[] = array_merge($item, ['precio_real' => (float) $precioReal]);
                }

                $gastosEnvio = $subtotal >= 50 ? 0.00 : 2.50;
                $total       = $subtotal + $gastosEnvio;

                // Verificar saldo RustiCoin si se usa ese método
                if ($metodoPago === 'rusticoin') {
                    $user->refresh(); // saldo actualizado
                    if ((float) $user->rusticoin_balance < $total) {
                        throw new \Exception("Saldo insuficiente en tu monedero RustiCoin. Necesitas {$total} RC, tienes " . number_format($user->rusticoin_balance, 2) . ' RC.');
                    }
                }

                // Crear el pedido
                $pedido = Pedido::create([
                    'user_id'           => $user->id,
                    'numero_pedido'     => Pedido::generateOrderNumber(),
                    'estado'            => 'pendiente',
                    'subtotal'          => $subtotal,
                    'gastos_envio'      => $gastosEnvio,
                    'total'             => $total,
                    'direccion_envio'   => $validated['direccion_envio'],
                    'telefono_contacto' => $validated['telefono_contacto'],
                    'notas'             => $validated['notas'] ?? null,
                    'metodo_pago'       => $metodoPago,
                ]);

                // Crear items y decrementar stock
                foreach ($itemsConPrecio as $item) {
                    $pedido->items()->create([
                        'producto_id'    => $item['id'],
                        'tienda_id'      => $item['tienda_id'],
                        'producto_nombre'=> $item['nombre'],
                        'tienda_nombre'  => $item['tienda_nombre'],
                        'producto_imagen'=> $item['imagen'] ?? null,
                        'cantidad'       => $item['cantidad'],
                        'precio_unitario'=> $item['precio_real'],
                        'subtotal'       => $item['precio_real'] * $item['cantidad'],
                    ]);

                    Producto::find($item['id'])->decrement('stock', $item['cantidad']);
                }

                // Descontar RustiCoins si aplica
                if ($metodoPago === 'rusticoin') {
                    $nuevoSaldo = (float) $user->rusticoin_balance - $total;
                    $user->forceFill(['rusticoin_balance' => $nuevoSaldo])->save();

                    RusticoinTransaction::create([
                        'user_id'       => $user->id,
                        'tipo'          => 'compra',
                        'cantidad'      => -$total,
                        'saldo_despues' => $nuevoSaldo,
                        'descripcion'   => "Pago pedido #{$pedido->numero_pedido}",
                        'pedido_id'     => $pedido->id,
                    ]);
                }

                return $pedido;
            });

            // Enviar email de confirmación al comprador
            $pedido->load(['items.tienda.user', 'user']);
            Mail::to($pedido->user->email)->send(new PedidoConfirmado($pedido));

            // Notificar a los owners de las tiendas involucradas
            $tiendasNotificadas = [];
            foreach ($pedido->items as $item) {
                if ($item->tienda && $item->tienda->user_id && !in_array($item->tienda->user_id, $tiendasNotificadas)) {
                    $tiendasNotificadas[] = $item->tienda->user_id;
                    Notificacion::enviar(
                        $item->tienda->user_id,
                        'nuevo_pedido',
                        'Nuevo pedido recibido',
                        "Has recibido el pedido #{$pedido->numero_pedido} por " . number_format($pedido->total, 2) . "€",
                        route('owner.panel'),
                        'cart',
                        'green'
                    );
                }
            }

            // Notificar a los admins
            Notificacion::enviarAdmins(
                'nuevo_pedido',
                'Nuevo pedido en la plataforma',
                "Pedido #{$pedido->numero_pedido} de {$pedido->user->name} por " . number_format($pedido->total, 2) . "€",
                route('admin.pedidos.index'),
                'cart',
                'primary'
            );

            return redirect()
                ->route('pedidos.confirmacion', $pedido)
                ->with('success', '¡Pedido realizado con éxito!');

        } catch (\Exception $e) {
            return back()->withErrors(['stock' => $e->getMessage()]);
        }
    }

    /**
     * Mostrar página de confirmación de un pedido.
     */
    public function show(Pedido $pedido)
    {
        abort_unless($pedido->user_id === auth()->id(), 403);

        $pedido->load(['items.producto', 'items.tienda']);

        return Inertia::render('PedidoConfirmacion', [
            'pedido' => $pedido,
        ]);
    }

    /**
     * Listado de pedidos del usuario autenticado.
     */
    public function index()
    {
        $userId = auth()->id();

        $pedidosActivos = Pedido::where('user_id', $userId)
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->with(['items.tienda:id,nombre,slug'])
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->get();

        $pedidosHistorial = Pedido::where('user_id', $userId)
            ->whereIn('estado', ['entregado', 'cancelado'])
            ->with(['items.tienda:id,nombre,slug'])
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $tiendaIdsDelivered = PedidoItem::whereHas('pedido', fn($q) =>
            $q->where('user_id', $userId)->where('estado', 'entregado')
        )->pluck('tienda_id')->unique();

        $yaReseniados = Resena::where('user_id', $userId)
            ->whereIn('tienda_id', $tiendaIdsDelivered)
            ->pluck('tienda_id');

        $reviewableStoreIds = $tiendaIdsDelivered->diff($yaReseniados)->values();

        return Inertia::render('MisPedidos', [
            'pedidosActivos'    => $pedidosActivos,
            'pedidosHistorial'  => $pedidosHistorial,
            'reviewableStoreIds'=> $reviewableStoreIds,
        ]);
    }

    /**
     * Cancelar un pedido y gestionar el reembolso.
     */
    public function cancelar(Request $request, Pedido $pedido)
    {
        // Verificar que el pedido pertenece al usuario autenticado
        if ($pedido->user_id !== auth()->id()) {
            abort(403);
        }

        // Solo se pueden cancelar pedidos en estado pendiente (antes de que el supplier confirme)
        if ($pedido->estado !== 'pendiente') {
            return back()->with('error', 'Solo puedes cancelar el pedido mientras esté pendiente de confirmación.');
        }

        $validated = $request->validate([
            'tipo_reembolso' => 'required|in:tarjeta,rusticoin',
        ]);

        // Reembolso Stripe si aplica (fuera de la transacción DB para no bloquearla en caso de error de red)
        $stripeReembolsoOk = false;
        if ($validated['tipo_reembolso'] === 'tarjeta' && $pedido->stripe_payment_intent_id && $pedido->metodo_pago === 'tarjeta') {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                $session = StripeSession::retrieve($pedido->stripe_payment_intent_id);
                if ($session->payment_intent) {
                    \Stripe\Refund::create(['payment_intent' => $session->payment_intent]);
                    $stripeReembolsoOk = true;
                }
            } catch (\Throwable $e) {
                Log::error('[Stripe refund] Pedido #' . $pedido->numero_pedido . ': ' . $e->getMessage());
                // No interrumpir la cancelación si falla el reembolso
            }
        }

        DB::transaction(function () use ($pedido, $validated) {
            $pedido->update(['estado' => 'cancelado']);

            // Si el reembolso es a RustiCoin, añadir al saldo del usuario
            if ($validated['tipo_reembolso'] === 'rusticoin' && $pedido->total > 0) {
                $user = $pedido->user;
                $nuevoSaldo = (float) $user->rusticoin_balance + (float) $pedido->total;
                $user->forceFill(['rusticoin_balance' => $nuevoSaldo])->save();

                RusticoinTransaction::create([
                    'user_id'       => $user->id,
                    'tipo'          => 'reembolso',
                    'cantidad'      => (float) $pedido->total,
                    'saldo_despues' => $nuevoSaldo,
                    'descripcion'   => "Reembolso por cancelación del pedido #{$pedido->numero_pedido}",
                    'pedido_id'     => $pedido->id,
                ]);
            }
        });

        if ($validated['tipo_reembolso'] === 'rusticoin') {
            $mensaje = "Pedido cancelado. Se han añadido " . number_format($pedido->total, 2) . " RC a tu monedero RustiCoin.";
        } elseif ($stripeReembolsoOk) {
            $mensaje = "Pedido cancelado. El reembolso se ha procesado automáticamente a tu tarjeta y puede tardar 5-10 días en aparecer.";
        } else {
            $mensaje = "Pedido cancelado. El reembolso a tu tarjeta se procesará en 5-10 días hábiles.";
        }

        // Notificar al usuario
        Notificacion::enviar(
            $pedido->user_id,
            'pedido_cancelado',
            'Pedido cancelado',
            $mensaje,
            route('pedidos.index'),
            'x',
            'red'
        );

        // Notificar a admins
        Notificacion::enviarAdmins(
            'pedido_cancelado',
            'Pedido cancelado por el cliente',
            "El pedido #{$pedido->numero_pedido} ha sido cancelado por el cliente.",
            route('admin.pedidos.show', $pedido),
            'x',
            'orange'
        );

        // Email al cliente con plantilla bonita
        try {
            \Illuminate\Support\Facades\Mail::to($pedido->user->email)
                ->send(new \App\Mail\PedidoCancelado($pedido->fresh(['items', 'user']), $validated['tipo_reembolso']));
        } catch (\Throwable $e) {
            // No interrumpir el flujo si falla el email
        }

        return redirect()->route('pedidos.index')->with('success', $mensaje);
    }
}
