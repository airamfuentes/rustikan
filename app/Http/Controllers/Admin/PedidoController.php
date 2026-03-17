<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $query = Pedido::with(['user', 'items.producto', 'items.tienda'])
            ->orderBy('created_at', 'desc');

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por rango de fecha
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        // Filtro por rango de precio
        if ($request->filled('precio_min')) {
            $query->where('total', '>=', $request->precio_min);
        }
        if ($request->filled('precio_max')) {
            $query->where('total', '<=', $request->precio_max);
        }

        $pedidos = $query->paginate(20)->withQueryString();

        // Estadísticas
        $stats = [
            'total' => Pedido::count(),
            'pendientes' => Pedido::where('estado', 'pendiente')->count(),
            'en_proceso' => Pedido::where('estado', 'en_proceso')->count(),
            'completados' => Pedido::where('estado', 'completado')->count(),
            'cancelados' => Pedido::where('estado', 'cancelado')->count(),
            'ingresos_totales' => Pedido::where('estado', 'completado')->sum('total'),
        ];

        return Inertia::render('Admin/Pedidos/Index', [
            'pedidos' => $pedidos,
            'stats' => $stats,
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
            'estado' => 'required|in:pendiente,en_proceso,completado,cancelado',
        ]);

        $estadoAnterior = $pedido->estado;
        $pedido->update($validated);

        // Registrar actividad
        $iconos = [
            'pendiente' => '⌛',
            'en_proceso' => '🚚',
            'completado' => '✅',
            'cancelado' => '❌',
        ];
        
        $colores = [
            'pendiente' => 'yellow',
            'en_proceso' => 'blue',
            'completado' => 'green',
            'cancelado' => 'red',
        ];
        
        ActivityLog::log(
            'cambiar_estado_pedido',
            "Pedido #{$pedido->id} cambió de {$estadoAnterior} a {$validated['estado']}",
            $iconos[$validated['estado']] ?? '📋',
            $colores[$validated['estado']] ?? 'gray',
            $pedido,
            ['estado_anterior' => $estadoAnterior, 'estado_nuevo' => $validated['estado']]
        );

        return redirect()->back()->with('success', 'Estado del pedido actualizado correctamente.');
    }
}
