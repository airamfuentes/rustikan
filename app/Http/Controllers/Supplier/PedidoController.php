<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Notificacion;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PedidoController extends Controller
{
    private const ESTADOS_PERMITIDOS = ['en_preparacion', 'confirmado', 'enviado', 'incidencia'];

    public function index(Request $request)
    {
        $query = Pedido::with(['user', 'items.producto', 'items.tienda'])
            ->whereNotIn('estado', ['cancelado'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_pedido', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $pedidos = $query->paginate(20)->withQueryString();

        $stats = [
            'pendientes'      => Pedido::where('estado', 'pendiente')->count(),
            'en_preparacion'  => Pedido::where('estado', 'en_preparacion')->count(),
            'confirmados'     => Pedido::where('estado', 'confirmado')->count(),
            'enviados'        => Pedido::where('estado', 'enviado')->count(),
            'incidencias'     => Pedido::where('estado', 'incidencia')->count(),
        ];

        return Inertia::render('Supplier/Pedidos/Index', [
            'pedidos' => $pedidos,
            'stats'   => $stats,
            'filters' => $request->only(['estado', 'search']),
        ]);
    }

    public function show(Pedido $pedido)
    {
        $pedido->load(['user', 'items.producto', 'items.tienda']);

        return Inertia::render('Supplier/Pedidos/Detalle', [
            'pedido' => $pedido,
        ]);
    }

    public function cambiarEstado(Request $request, Pedido $pedido)
    {
        $request->validate([
            'estado' => 'required|in:' . implode(',', self::ESTADOS_PERMITIDOS),
        ]);

        if (in_array($pedido->estado, ['cancelado', 'entregado'])) {
            return back()->with('error', 'No se puede modificar un pedido cancelado o entregado.');
        }

        $estadoAnterior = $pedido->estado;
        $nuevoEstado    = $request->estado;

        $pedido->update(['estado' => $nuevoEstado]);

        ActivityLog::log(
            'supplier_cambiar_estado',
            "Pedido #{$pedido->numero_pedido} cambió de {$estadoAnterior} a {$nuevoEstado}",
            'editar',
            'blue',
            $pedido
        );

        // Notificar al cliente
        $mensajesEstado = [
            'en_preparacion' => 'Tu pedido está siendo preparado en nuestro almacén.',
            'confirmado'     => 'Tu pedido ha sido confirmado y está listo.',
            'enviado'        => 'Tu pedido ha sido enviado. ¡Pronto lo recibirás!',
            'incidencia'     => 'Ha ocurrido una incidencia con tu pedido. Nos pondremos en contacto contigo.',
        ];

        if ($pedido->user_id) {
            Notificacion::enviar(
                $pedido->user_id,
                'pedido_' . $nuevoEstado,
                'Actualización de tu pedido',
                $mensajesEstado[$nuevoEstado] ?? "Tu pedido ha pasado a estado: {$nuevoEstado}.",
                route('pedidos.index'),
                $nuevoEstado === 'incidencia' ? 'x' : 'cart',
                $nuevoEstado === 'incidencia' ? 'red' : 'green'
            );
        }

        // Notificar admins si incidencia
        if ($nuevoEstado === 'incidencia') {
            Notificacion::enviarAdmins(
                'incidencia_pedido',
                'Incidencia en pedido',
                "El pedido #{$pedido->numero_pedido} tiene una incidencia reportada por almacén.",
                route('admin.pedidos.show', $pedido),
                'x',
                'red'
            );
        }

        return back()->with('success', "Pedido actualizado a: {$nuevoEstado}");
    }
}
