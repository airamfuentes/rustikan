<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Notificacion;
use App\Models\Pedido;
use App\Models\RusticoinTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

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
                $query->whereIn('estado', ['confirmado', 'preparando', 'en_camino']);
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

        $numPedidoCancelar = $pedido->numero_pedido ?? $pedido->id;

        DB::transaction(function () use ($pedido, $tipoReembolso, $numPedidoCancelar) {
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
                    'tarjeta'   => ' El reembolso a tu tarjeta se procesará en 5-7 días hábiles.',
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
        });

        $msgFlash = 'Pedido cancelado correctamente.';
        if ($tipoReembolso === 'rusticoin') {
            $msgFlash .= ' RustiCoins abonados al cliente.';
        } elseif ($tipoReembolso === 'tarjeta') {
            $msgFlash .= ' Reembolso a tarjeta iniciado.';
        }

        return redirect()->route('admin.pedidos.show', $pedido)->with('success', $msgFlash);
    }
}
