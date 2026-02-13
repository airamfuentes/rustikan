<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tienda;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Usuarios recientes con estadísticas
        $usuarios_recientes = User::where('role', 'user')
            ->withCount('pedidos')
            ->withSum('pedidos', 'total')
            ->latest()
            ->take(10)
            ->get();

        // Total de usuarios
        $total_usuarios = User::where('role', 'user')->count();

        // Pedidos recientes con detalles completos
        $pedidos_recientes = Pedido::with(['user', 'items.producto', 'items.tienda'])
            ->latest()
            ->take(15)
            ->get();

        // Estadísticas de pedidos
        $pedidos_stats = [
            'total' => Pedido::count(),
            'pendientes' => Pedido::where('estado', 'pendiente')->count(),
            'en_proceso' => Pedido::whereIn('estado', ['confirmado', 'preparando', 'en_camino'])->count(),
            'completados' => Pedido::where('estado', 'entregado')->count(),
            'cancelados' => Pedido::where('estado', 'cancelado')->count(),
            'ingresos_mes' => Pedido::where('estado', 'entregado')
                ->whereMonth('created_at', now()->month)
                ->sum('total'),
            'ingresos_totales' => Pedido::where('estado', 'entregado')->sum('total'),
        ];

        // Estadísticas de tiendas y productos
        $tiendas_stats = [
            'total' => Tienda::count(),
            'activas' => Tienda::where('activa', true)->count(),
            'visibles' => Tienda::where('visible', true)->count(),
        ];

        $productos_stats = [
            'total' => Producto::count(),
            'disponibles' => Producto::where('disponible', true)->count(),
            'sin_stock' => Producto::where('stock', 0)->count(),
            'bajo_stock' => Producto::whereRaw('stock <= stock_minimo')->where('stock', '>', 0)->count(),
        ];

        // Productos con alertas de stock
        $productos_bajo_stock = Producto::with(['tienda', 'categoria'])
            ->whereRaw('stock <= stock_minimo')
            ->where('stock', '>', 0)
            ->orderBy('stock', 'asc')
            ->take(10)
            ->get();

        $productos_sin_stock = Producto::with(['tienda', 'categoria'])
            ->where('stock', 0)
            ->take(10)
            ->get();

        // Actividad reciente con filtros
        $actividadQuery = ActivityLog::with('user')->latest();
        
        // Filtro por rango de fechas
        if ($request->filled('fecha_desde')) {
            $actividadQuery->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $actividadQuery->whereDate('created_at', '<=', $request->fecha_hasta);
        }
        
        $actividad_reciente = $actividadQuery
            ->take(15)
            ->get()
            ->map(fn($log) => [
                'tipo' => $log->tipo,
                'descripcion' => $log->descripcion,
                'fecha' => $log->created_at,
                'icono' => $log->icono,
                'color' => $log->color,
                'usuario' => $log->user?->name ?? 'Sistema',
            ]);

        return Inertia::render('Admin/Dashboard', [
            'usuarios_recientes' => $usuarios_recientes,
            'total_usuarios' => $total_usuarios,
            'pedidos_recientes' => $pedidos_recientes,
            'pedidos_stats' => $pedidos_stats,
            'tiendas_stats' => $tiendas_stats,
            'productos_stats' => $productos_stats,
            'productos_bajo_stock' => $productos_bajo_stock,
            'productos_sin_stock' => $productos_sin_stock,
            'actividad_reciente' => $actividad_reciente,
            'filtros_aplicados' => $request->only(['fecha_desde', 'fecha_hasta']),
        ]);
    }
}
