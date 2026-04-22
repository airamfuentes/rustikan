<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\PedidoItem;
use App\Models\Pedido;
use App\Models\SolicitudCambio;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class PanelController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $tienda = $user->tiendas()->with('categoria')->firstOrFail();

        // ── Stats ────────────────────────────────────────────────────────────────
        $totalIngresos = PedidoItem::where('tienda_id', $tienda->id)
            ->sum('subtotal');

        $totalPedidos = PedidoItem::where('tienda_id', $tienda->id)
            ->distinct('pedido_id')
            ->count('pedido_id');

        $totalProductos = $tienda->productos()->count();

        $productosDisponibles = $tienda->productos()->where('disponible', true)->count();

        // ── Gráfica: ingresos últimos 30 días ────────────────────────────────────
        $inicio = Carbon::now()->subDays(29)->startOfDay();

        $ingresosBrutos = PedidoItem::where('tienda_id', $tienda->id)
            ->where('created_at', '>=', $inicio)
            ->groupBy('fecha')
            ->select(DB::raw('DATE(created_at) as fecha'), DB::raw('SUM(subtotal) as total'))
            ->orderBy('fecha')
            ->pluck('total', 'fecha');

        // Rellenar los 30 días (con 0 si no hay ventas)
        $ingresosGrafica = [];
        for ($i = 29; $i >= 0; $i--) {
            $fecha = Carbon::now()->subDays($i)->format('Y-m-d');
            $ingresosGrafica[] = [
                'fecha'  => Carbon::now()->subDays($i)->format('d/m'),
                'total'  => (float) ($ingresosBrutos[$fecha] ?? 0),
            ];
        }

        // ── Top 5 productos ───────────────────────────────────────────────────────
        $topProductos = PedidoItem::where('tienda_id', $tienda->id)
            ->groupBy('producto_id', 'producto_nombre')
            ->select(
                'producto_id',
                'producto_nombre',
                DB::raw('SUM(cantidad) as total_vendidos'),
                DB::raw('SUM(subtotal) as total_ingresos')
            )
            ->orderByDesc('total_vendidos')
            ->limit(5)
            ->get()
            ->map(function ($item) use ($tienda) {
                // Attach product image if still exists
                $producto = $tienda->productos()->find($item->producto_id);
                $item->imagen = $producto?->imagen;
                return $item;
            });

        // ── Pedidos recientes ─────────────────────────────────────────────────────
        $pedidosRecientes = Pedido::whereHas('items', fn($q) => $q->where('tienda_id', $tienda->id))
            ->with([
                'user:id,name,email',
                'items' => fn($q) => $q->where('tienda_id', $tienda->id),
            ])
            ->latest()
            ->limit(8)
            ->get()
            ->map(function ($pedido) {
                return [
                    'id'              => $pedido->id,
                    'numero_pedido'   => $pedido->numero_pedido,
                    'estado'          => $pedido->estado,
                    'total_tienda'    => $pedido->items->sum('subtotal'),
                    'items_count'     => $pedido->items->count(),
                    'cliente'         => $pedido->user?->name ?? 'Cliente',
                    'created_at'      => $pedido->created_at->format('d/m/Y H:i'),
                ];
            });

        // ── Todos los productos para la tabla ────────────────────────────────────
        $productos = $tienda->productos()
            ->with('categoria')
            ->orderByDesc('destacado')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'precio', 'precio_oferta', 'oferta_activa', 'stock', 'disponible', 'destacado', 'imagen', 'categoria_id', 'descripcion', 'unidad']);

        // ── Categorías para el formulario de producto ─────────────────────────────
        $categorias = Categoria::orderBy('nombre')->get(['id', 'nombre']);

        // ── Solicitudes pendientes + recientes ────────────────────────────────────
        $solicitudes = SolicitudCambio::where('tienda_id', $tienda->id)
            ->with(['producto:id,nombre,imagen', 'revisor:id,name'])
            ->orderByRaw("FIELD(estado, 'pendiente', 'aprobado', 'rechazado')")
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($s) => [
                'id'             => $s->id,
                'tipo'           => $s->tipo,
                'campo'          => $s->campo,
                'label_campo'    => $s->labelCampo(),
                'valor_anterior' => $s->valor_anterior,
                'valor_nuevo'    => $s->valor_nuevo,
                'estado'         => $s->estado,
                'motivo_rechazo' => $s->motivo_rechazo,
                'revisor'        => $s->revisor?->name,
                'revisado_at'    => $s->revisado_at?->format('d/m/Y H:i'),
                'created_at'     => $s->created_at->format('d/m/Y H:i'),
                'producto'       => $s->producto ? ['id' => $s->producto->id, 'nombre' => $s->producto->nombre] : null,
            ]);

        // ── Ingresos este mes vs mes anterior ─────────────────────────────────────
        $ingresosMesActual = PedidoItem::where('tienda_id', $tienda->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('subtotal');

        $ingresosMesAnterior = PedidoItem::where('tienda_id', $tienda->id)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('subtotal');

        $crecimiento = $ingresosMesAnterior > 0
            ? round((($ingresosMesActual - $ingresosMesAnterior) / $ingresosMesAnterior) * 100, 1)
            : ($ingresosMesActual > 0 ? 100 : 0);

        return Inertia::render('Owner/Panel', [
            'tienda'               => $tienda,
            'stats'                => [
                'totalIngresos'        => round((float) $totalIngresos, 2),
                'totalPedidos'         => $totalPedidos,
                'totalProductos'       => $totalProductos,
                'productosDisponibles' => $productosDisponibles,
                'valoracion'           => (float) $tienda->valoracion,
                'totalResenas'         => $tienda->total_resenas,
                'ingresosMesActual'    => round((float) $ingresosMesActual, 2),
                'crecimiento'          => $crecimiento,
            ],
            'ingresosGrafica'      => $ingresosGrafica,
            'topProductos'         => $topProductos,
            'pedidosRecientes'     => $pedidosRecientes,
            'productos'            => $productos,
            'categorias'           => $categorias,
            'solicitudes'          => $solicitudes,
        ]);
    }
}
