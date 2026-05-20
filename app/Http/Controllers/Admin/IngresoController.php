<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class IngresoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Filtros de rango de fechas (por defecto: inicio a fin del año actual)
        $fecha_desde = $request->input('fecha_desde', now()->startOfYear()->format('Y-m-d'));
        $fecha_hasta = $request->input('fecha_hasta', now()->endOfYear()->format('Y-m-d'));

        $comisionPct = 10.0;

        $estadosActivos = ['entregado', 'confirmado', 'en_preparacion', 'enviado'];

        $ingresosTotales = (float) Pedido::whereIn('estado', $estadosActivos)
            ->whereBetween('created_at', [$fecha_desde, $fecha_hasta])
            ->sum('total');
        $ingresosPeriodo = $ingresosTotales;

        $stats = [
            'ingresos_totales'      => $ingresosTotales,
            'ingresos_periodo'      => $ingresosPeriodo,
            'pedidos_completados'   => Pedido::whereIn('estado', $estadosActivos)
                ->whereBetween('created_at', [$fecha_desde, $fecha_hasta])
                ->count(),
            'ticket_promedio'       => Pedido::whereIn('estado', $estadosActivos)
                ->whereBetween('created_at', [$fecha_desde, $fecha_hasta])
                ->avg('total') ?? 0,
            'ingresos_mes_actual'   => Pedido::whereIn('estado', $estadosActivos)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total'),
            'ingresos_mes_anterior' => Pedido::whereIn('estado', $estadosActivos)
                ->whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->sum('total'),
            'comision_pct'          => $comisionPct,
        ];

        // Calcular crecimiento mensual
        if ($stats['ingresos_mes_anterior'] > 0) {
            $stats['crecimiento_mensual'] = (($stats['ingresos_mes_actual'] - $stats['ingresos_mes_anterior']) / $stats['ingresos_mes_anterior']) * 100;
        } else {
            $stats['crecimiento_mensual'] = 0;
        }

        // Ingresos por mes (últimos 12 meses dentro del periodo filtrado)
        $ingresos_mensuales = Pedido::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'),
            DB::raw('SUM(total) as total'),
            DB::raw('COUNT(*) as cantidad')
        )
        ->whereIn('estado', $estadosActivos)
        ->whereBetween('created_at', [$fecha_desde, $fecha_hasta])
        ->groupBy('mes')
        ->orderBy('mes', 'asc')
        ->get();

        // Ingresos por tienda (top 10)
        $ingresos_por_tienda = DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->join('tiendas', 'pedido_items.tienda_id', '=', 'tiendas.id')
            ->select(
                'tiendas.id',
                'tiendas.nombre',
                'tiendas.logo',
                DB::raw('SUM(pedido_items.subtotal) as total_ingresos'),
                DB::raw('COUNT(DISTINCT pedidos.id) as total_pedidos')
            )
            ->whereIn('pedidos.estado', $estadosActivos)
            ->whereBetween('pedidos.created_at', [$fecha_desde, $fecha_hasta])
            ->groupBy('tiendas.id', 'tiendas.nombre', 'tiendas.logo')
            ->orderBy('total_ingresos', 'desc')
            ->limit(10)
            ->get();

        // Ingresos por categoría
        $ingresos_por_categoria = DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->join('tiendas', 'pedido_items.tienda_id', '=', 'tiendas.id')
            ->leftJoin('categorias', 'tiendas.categoria_id', '=', 'categorias.id')
            ->select(
                DB::raw('COALESCE(categorias.nombre, "Sin categoría") as categoria'),
                'categorias.icono',
                'categorias.imagen',
                DB::raw('SUM(pedido_items.subtotal) as total_ingresos'),
                DB::raw('COUNT(DISTINCT pedidos.id) as total_pedidos')
            )
            ->whereIn('pedidos.estado', $estadosActivos)
            ->whereBetween('pedidos.created_at', [$fecha_desde, $fecha_hasta])
            ->groupBy('categorias.id', 'categorias.nombre', 'categorias.icono', 'categorias.imagen')
            ->orderBy('total_ingresos', 'desc')
            ->get();

        // Productos más vendidos (top 10) con tienda
        $productos_top = DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->join('productos', 'pedido_items.producto_id', '=', 'productos.id')
            ->join('tiendas', 'pedido_items.tienda_id', '=', 'tiendas.id')
            ->select(
                'productos.id',
                'productos.nombre',
                'productos.imagen',
                'tiendas.nombre as tienda_nombre',
                DB::raw('SUM(pedido_items.cantidad) as total_vendido'),
                DB::raw('SUM(pedido_items.subtotal) as total_ingresos')
            )
            ->whereIn('pedidos.estado', $estadosActivos)
            ->whereBetween('pedidos.created_at', [$fecha_desde, $fecha_hasta])
            ->groupBy('productos.id', 'productos.nombre', 'productos.imagen', 'tiendas.id', 'tiendas.nombre')
            ->orderBy('total_ingresos', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Ingresos/Index', [
            'stats' => $stats,
            'ingresos_mensuales' => $ingresos_mensuales,
            'ingresos_por_tienda' => $ingresos_por_tienda,
            'ingresos_por_categoria' => $ingresos_por_categoria,
            'productos_top' => $productos_top,
            'filters' => [
                'fecha_desde' => $fecha_desde,
                'fecha_hasta' => $fecha_hasta,
            ],
        ]);
    }
}
