<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tienda;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

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

        // Estadísticas de tiendas
        $tiendas_stats = [
            'total' => Tienda::count(),
            'activas' => Tienda::where('activa', true)->count(),
            'visibles' => Tienda::where('visible', true)->count(),
        ];

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

        // ── Beneficios / comisiones Rustikan (10%) ────────────────────────────────
        $comisionPct = 10.0;

        $comisionesPorMes = [];
        for ($i = 11; $i >= 0; $i--) {
            $fecha = Carbon::now()->subMonths($i);
            $bruto = (float) PedidoItem::whereHas('pedido', fn($q) => $q->where('estado', 'entregado'))
                ->whereMonth('created_at', $fecha->month)
                ->whereYear('created_at', $fecha->year)
                ->sum('subtotal');
            $comision = round($bruto * $comisionPct / 100, 2);
            $comisionesPorMes[] = [
                'mes'      => $fecha->format('M y'),
                'bruto'    => round($bruto, 2),
                'comision' => $comision,
                'neto'     => round($bruto - $comision, 2),
            ];
        }

        $totalBrutoPlataforma = (float) PedidoItem::whereHas('pedido', fn($q) => $q->where('estado', 'entregado'))->sum('subtotal');
        $totalComisionPlataforma = round($totalBrutoPlataforma * $comisionPct / 100, 2);

        $comisionesPorTienda = Tienda::withSum(['pedidoItems as bruto_tienda' => fn($q) => $q->whereHas('pedido', fn($p) => $p->where('estado', 'entregado'))], 'subtotal')
            ->orderByDesc('bruto_tienda')
            ->limit(10)
            ->get(['id', 'nombre', 'logo'])
            ->map(fn($t) => [
                'id'       => $t->id,
                'nombre'   => $t->nombre,
                'logo'     => $t->logo,
                'bruto'    => round((float) $t->bruto_tienda, 2),
                'comision' => round((float) $t->bruto_tienda * $comisionPct / 100, 2),
                'neto'     => round((float) $t->bruto_tienda * (1 - $comisionPct / 100), 2),
            ]);

        return Inertia::render('Admin/Panel', [
            'usuarios_recientes'       => $usuarios_recientes,
            'total_usuarios'           => $total_usuarios,
            'pedidos_recientes'        => $pedidos_recientes,
            'pedidos_stats'            => $pedidos_stats,
            'tiendas_stats'            => $tiendas_stats,
            'actividad_reciente'       => $actividad_reciente,
            'filtros_aplicados'        => $request->only(['fecha_desde', 'fecha_hasta']),
            'comision_pct'             => $comisionPct,
            'comisiones_por_mes'       => $comisionesPorMes,
            'total_bruto_plataforma'   => round($totalBrutoPlataforma, 2),
            'total_comision_plataforma'=> $totalComisionPlataforma,
            'comisiones_por_tienda'    => $comisionesPorTienda,
        ]);
    }
}
