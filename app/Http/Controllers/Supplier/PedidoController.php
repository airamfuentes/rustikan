<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Jobs\MarcarPedidoEntregado;
use App\Models\ActivityLog;
use App\Models\Notificacion;
use App\Models\Pedido;
use App\Mail\PedidoConfirmado;
use App\Mail\PedidoEnviado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class PedidoController extends Controller
{
    private const ESTADOS_PERMITIDOS = ['confirmado', 'en_preparacion', 'enviado', 'incidencia'];

    public function salidasIndex(Request $request)
    {
        $query = Pedido::with(['user:id,name,email', 'items.producto:id,nombre,stock,stock_minimo,unidad,imagen', 'items.tienda:id,nombre'])
            ->where('estado', 'en_preparacion')
            ->orderBy('created_at', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_pedido', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $pedidos = $query->get();

        return Inertia::render('Supplier/Salidas/Index', [
            'pedidos' => $pedidos,
            'filters' => $request->only(['search']),
        ]);
    }

    public function darSalida(Request $request)
    {
        $request->validate([
            'pedido_ids'   => ['required', 'array', 'min:1'],
            'pedido_ids.*' => ['exists:pedidos,id'],
        ]);

        $pedidos = Pedido::with(['user', 'items'])
            ->whereIn('id', $request->pedido_ids)
            ->where('estado', 'en_preparacion')
            ->get();

        $procesados = 0;
        foreach ($pedidos as $pedido) {
            $estadoAnterior = $pedido->estado;
            $pedido->update(['estado' => 'enviado']);
            $procesados++;

            MarcarPedidoEntregado::dispatch($pedido->id)->delay(now()->addSeconds(30));

            ActivityLog::log(
                'supplier_dar_salida',
                "Pedido #{$pedido->numero_pedido} marcado como enviado ({$estadoAnterior} → enviado)",
                'editar',
                'purple',
                $pedido
            );

            if ($pedido->user_id) {
                Notificacion::enviar(
                    $pedido->user_id,
                    'pedido_enviado',
                    'Tu pedido está en camino',
                    '¡Tu pedido ha sido enviado! El repartidor ya lo tiene. ¡Pronto lo recibirás!',
                    route('pedidos.index'),
                    'cart',
                    'purple'
                );

                if ($pedido->user?->email) {
                    try {
                        Mail::to($pedido->user->email)->send(new PedidoEnviado($pedido));
                    } catch (\Throwable) {}
                }
            }
        }

        return back()->with('success', $procesados === 1
            ? "Pedido marcado como enviado correctamente."
            : "{$procesados} pedidos marcados como enviados correctamente."
        );
    }

    public function index(Request $request)
    {
        $query = Pedido::with(['user:id,name,email', 'items.producto:id,nombre,stock,stock_minimo,unidad,imagen', 'items.tienda:id,nombre'])
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
            'pedido'    => $pedido,
            'facturaUrl' => route('supplier.exportar.pedido', $pedido),
        ]);
    }

    public function historial(Request $request)
    {
        $query = Pedido::with(['user:id,name,email', 'items.producto', 'items.tienda'])
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

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $pedidos = $query->paginate(25)->withQueryString();

        return Inertia::render('Supplier/Historial', [
            'pedidos' => $pedidos,
            'filters' => $request->only(['estado', 'search', 'fecha_desde', 'fecha_hasta']),
        ]);
    }

    public function cambiarEstado(Request $request, Pedido $pedido)
    {
        $request->validate([
            'estado'            => 'required|in:' . implode(',', self::ESTADOS_PERMITIDOS),
            'motivo_incidencia' => 'required_if:estado,incidencia|nullable|string|max:500',
        ]);

        if ($pedido->estado === 'cancelado') {
            return back()->with('error', 'No se puede modificar un pedido cancelado.');
        }

        $estadoAnterior = $pedido->estado;
        $nuevoEstado    = $request->estado;

        // Confirmar → auto-avanza a en_preparacion
        if ($nuevoEstado === 'confirmado') {
            $nuevoEstado = 'en_preparacion';
        }

        $updateData = ['estado' => $nuevoEstado];
        if ($nuevoEstado === 'incidencia') {
            $updateData['motivo_incidencia'] = $request->motivo_incidencia;
        }
        $pedido->update($updateData);

        if ($nuevoEstado === 'enviado') {
            MarcarPedidoEntregado::dispatch($pedido->id)->delay(now()->addSeconds(30));
        }

        ActivityLog::log(
            'supplier_cambiar_estado',
            "Pedido #{$pedido->numero_pedido} cambió de {$estadoAnterior} a {$nuevoEstado}",
            'editar',
            'blue',
            $pedido
        );

        // Notificar al cliente
        $mensajesEstado = [
            'confirmado'     => 'Tu pedido ha sido confirmado por el almacén.',
            'en_preparacion' => 'Tu pedido está siendo preparado en nuestro almacén.',
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

        // Enviar email al cliente según estado
        if ($pedido->user?->email) {
            try {
                if ($nuevoEstado === 'en_preparacion') {
                    $pedido->load(['user', 'items']);
                    Mail::to($pedido->user->email)->send(new PedidoConfirmado($pedido));
                } elseif ($nuevoEstado === 'enviado') {
                    $pedido->load(['user', 'items']);
                    Mail::to($pedido->user->email)->send(new PedidoEnviado($pedido));
                }
            } catch (\Throwable) {}
        }

        $labels = [
            'en_preparacion' => 'En preparación',
            'enviado'        => 'Enviado',
            'incidencia'     => 'Incidencia',
        ];

        return back()->with('success', "Pedido actualizado: " . ($labels[$nuevoEstado] ?? $nuevoEstado));
    }
}
