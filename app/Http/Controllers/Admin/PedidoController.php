<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Notificacion;
use App\Models\Pedido;
use App\Models\RusticoinTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $query = Pedido::with(['user', 'items.producto', 'items.tienda'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('numero_pedido', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'en_proceso') {
                $query->whereIn('estado', ['confirmado', 'en_preparacion', 'preparando', 'en_camino', 'enviado']);
            } else {
                $query->where('estado', $request->estado);
            }
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }
        if ($request->filled('precio_min')) {
            $query->where('total', '>=', $request->precio_min);
        }
        if ($request->filled('precio_max')) {
            $query->where('total', '<=', $request->precio_max);
        }

        $pedidos = $query->paginate(20)->withQueryString();

        $stats = [
            'total'            => Pedido::count(),
            'pendientes'       => Pedido::where('estado', 'pendiente')->count(),
            'en_proceso'       => Pedido::whereIn('estado', ['confirmado', 'en_preparacion', 'preparando', 'en_camino', 'enviado'])->count(),
            'completados'      => Pedido::where('estado', 'entregado')->count(),
            'cancelados'       => Pedido::where('estado', 'cancelado')->count(),
            'incidencias'      => Pedido::where('estado', 'incidencia')->count(),
            'ingresos_totales' => Pedido::where('estado', 'entregado')->sum('total'),
        ];

        return Inertia::render('Admin/Pedidos/Index', [
            'pedidos' => $pedidos,
            'stats'   => $stats,
            'filters' => $request->only(['search', 'estado', 'fecha_desde', 'fecha_hasta', 'precio_min', 'precio_max']),
        ]);
    }

    public function show(Pedido $pedido)
    {
        $pedido->load(['user', 'items.producto', 'items.tienda']);

        return Inertia::render('Admin/Pedidos/Detalle', [
            'pedido' => $pedido,
        ]);
    }

    public function update(Request $request, Pedido $pedido)
    {
        if (in_array($pedido->estado, ['entregado', 'cancelado'], true)) {
            return back()->with('error', 'No se pueden editar los datos de un pedido ya '
                . ($pedido->estado === 'entregado' ? 'entregado' : 'cancelado') . '.');
        }

        $validated = $request->validate([
            'telefono_contacto' => 'nullable|string|max:30',
            'direccion_envio'   => 'nullable|string|max:500',
            'notas'             => 'nullable|string|max:1000',
        ]);

        $pedido->update($validated);

        $numPedido = $pedido->numero_pedido ?? $pedido->id;
        ActivityLog::log(
            'editar_pedido_admin',
            "Pedido #{$numPedido} editado por admin",
            'editar',
            'blue',
            $pedido
        );

        return redirect()->back()->with('success', 'Pedido actualizado correctamente.');
    }

    public function cancelar(Request $request, Pedido $pedido)
    {
        if ($pedido->estado === 'cancelado') {
            return back()->with('error', 'Este pedido ya está cancelado.');
        }
        if ($pedido->estado === 'entregado') {
            return back()->with('error', 'No se puede cancelar un pedido ya entregado.');
        }

        $tipoReembolso = $request->input('tipo_reembolso', 'ninguno'); // ninguno, tarjeta, rusticoin

        $pedido->loadMissing(['user', 'items.tienda']);
        $numPedidoCancelar = $pedido->numero_pedido ?? $pedido->id;

        // Reembolso Stripe si aplica (fuera de la transacción DB para no bloquearla en caso de error de red)
        $stripeReembolsoOk = false;
        if ($tipoReembolso === 'tarjeta' && $pedido->stripe_payment_intent_id && $pedido->metodo_pago === 'tarjeta') {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                $session = StripeSession::retrieve($pedido->stripe_payment_intent_id);
                if ($session->payment_intent) {
                    \Stripe\Refund::create(['payment_intent' => $session->payment_intent]);
                    $stripeReembolsoOk = true;
                }
            } catch (\Throwable $e) {
                Log::error('[Stripe refund admin] Pedido #' . $numPedidoCancelar . ': ' . $e->getMessage());
                // No interrumpir la cancelación si falla el reembolso
            }
        }

        DB::transaction(function () use ($pedido, $tipoReembolso, $numPedidoCancelar, $stripeReembolsoOk) {
            $pedido->update(['estado' => 'cancelado']);

            if ($tipoReembolso === 'rusticoin' && $pedido->user_id && $pedido->total > 0) {
                $user = $pedido->user;
                $user->increment('rusticoin_balance', $pedido->total);

                RusticoinTransaction::create([
                    'user_id'       => $pedido->user_id,
                    'tipo'          => 'reembolso',
                    'cantidad'      => $pedido->total,
                    'saldo_despues' => $user->fresh()->rusticoin_balance,
                    'pedido_id'     => $pedido->id,
                    'descripcion'   => "Reembolso por cancelación de pedido #{$numPedidoCancelar}",
                ]);
            }

            ActivityLog::log(
                'cancelar_pedido_admin',
                "Pedido #{$numPedidoCancelar} cancelado por admin (reembolso: {$tipoReembolso})",
                'cancelado',
                'red',
                $pedido
            );

            if ($pedido->user_id) {
                $msgReembolso = match ($tipoReembolso) {
                    'rusticoin' => ' Se han abonado ' . number_format($pedido->total, 2) . ' RC a tu monedero RustiCoin.',
                    'tarjeta'   => $stripeReembolsoOk
                        ? ' El reembolso se ha procesado automáticamente a tu tarjeta y puede tardar 5-10 días en aparecer.'
                        : ' El reembolso a tu tarjeta se procesará en 5-10 días hábiles.',
                    default     => '',
                };

                Notificacion::enviar(
                    $pedido->user_id,
                    'pedido_cancelado',
                    'Tu pedido ha sido cancelado',
                    "El administrador ha cancelado tu pedido #{$numPedidoCancelar}.{$msgReembolso}",
                    route('pedidos.index'),
                    'x',
                    'red'
                );
            }

            // Notificar a los owners de las tiendas afectadas
            $ownerIds = $pedido->items
                ->pluck('tienda.user_id')
                ->filter()
                ->unique()
                ->values();

            foreach ($ownerIds as $ownerId) {
                Notificacion::enviar(
                    $ownerId,
                    'pedido_cancelado_owner',
                    'Un pedido de tu tienda ha sido cancelado',
                    "El administrador ha cancelado el pedido #{$numPedidoCancelar} que incluía productos de tu tienda.",
                    route('supplier.pedidos.index'),
                    'x',
                    'orange'
                );
            }
        });

        $msgFlash = 'Pedido cancelado correctamente.';
        if ($tipoReembolso === 'rusticoin') {
            $msgFlash .= ' RustiCoins abonados al cliente.';
        } elseif ($tipoReembolso === 'tarjeta') {
            $msgFlash .= $stripeReembolsoOk
                ? ' Reembolso procesado automáticamente vía Stripe.'
                : ' Reembolso a tarjeta pendiente de procesar.';
        }

        // Email al cliente con plantilla
        try {
            if ($pedido->user) {
                \Illuminate\Support\Facades\Mail::to($pedido->user->email)
                    ->send(new \App\Mail\PedidoCancelado($pedido->fresh(['items', 'user']), $tipoReembolso, 'El administrador ha cancelado tu pedido.'));
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return redirect()->route('admin.pedidos.show', $pedido)->with('success', $msgFlash);
    }
}
