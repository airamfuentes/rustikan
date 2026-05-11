<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    /** Ingresos mensuales → PDF imprimible */
    public function ingresosPdf(Request $request)
    {
        $desde = $request->input('fecha_desde', now()->subYear()->format('Y-m-d'));
        $hasta = $request->input('fecha_hasta', now()->format('Y-m-d'));

        $rows = Pedido::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as pedidos')
            )
            ->where('estado', 'entregado')
            ->whereBetween('created_at', [$desde, $hasta])
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return view('pdfs.admin.ingresos', [
            'rows'  => $rows,
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
    }

    /** Ingresos por tienda → PDF imprimible */
    public function ingresosPorTiendaPdf(Request $request)
    {
        $desde = $request->input('fecha_desde', now()->subYear()->format('Y-m-d'));
        $hasta = $request->input('fecha_hasta', now()->format('Y-m-d'));

        $rows = DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->join('tiendas', 'pedido_items.tienda_id', '=', 'tiendas.id')
            ->select(
                'tiendas.nombre',
                DB::raw('SUM(pedido_items.subtotal) as total_ingresos'),
                DB::raw('COUNT(DISTINCT pedidos.id) as total_pedidos')
            )
            ->where('pedidos.estado', 'entregado')
            ->whereBetween('pedidos.created_at', [$desde, $hasta])
            ->groupBy('tiendas.id', 'tiendas.nombre')
            ->orderByDesc('total_ingresos')
            ->get();

        return view('pdfs.admin.ingresos-tiendas', [
            'rows'  => $rows,
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
    }

    /** Listado de pedidos → PDF imprimible */
    public function pedidosPdf(Request $request)
    {
        $estado = $request->input('estado', 'todos');

        $query = Pedido::with('user:id,name,email')
            ->select(['id', 'numero_pedido', 'user_id', 'estado', 'total', 'created_at', 'direccion_envio']);
        if ($estado !== 'todos') {
            $query->where('estado', $estado);
        }
        $pedidos = $query->orderByDesc('created_at')->get();

        return view('pdfs.admin.pedidos', [
            'pedidos' => $pedidos,
            'estado'  => $estado,
        ]);
    }
}
