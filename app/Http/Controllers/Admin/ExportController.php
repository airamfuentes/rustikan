<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    /** Ingresos mensuales → CSV */
    public function ingresosCsv(Request $request)
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

        $lines = ["Mes,Ingresos brutos (€),Comisión 10% (€),Beneficio neto (€),Pedidos completados"];
        foreach ($rows as $r) {
            $bruto    = round((float) $r->total, 2);
            $comision = round($bruto * 0.10, 2);
            $neto     = round($bruto - $comision, 2);
            $lines[]  = "{$r->mes},{$bruto},{$comision},{$neto},{$r->pedidos}";
        }

        return response(implode("\n", $lines), 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="ingresos_' . date('Y-m-d') . '.csv"',
        ]);
    }

    /** Ingresos por tienda → CSV */
    public function ingresosPorTiendaCsv(Request $request)
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

        $lines = ["Tienda,Ingresos brutos (€),Comisión 10% (€),Beneficio neto (€),Pedidos completados"];
        foreach ($rows as $r) {
            $bruto    = round((float) $r->total_ingresos, 2);
            $comision = round($bruto * 0.10, 2);
            $neto     = round($bruto - $comision, 2);
            $nombre   = '"' . str_replace('"', '""', $r->nombre) . '"';
            $lines[]  = "{$nombre},{$bruto},{$comision},{$neto},{$r->total_pedidos}";
        }

        return response(implode("\n", $lines), 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="ingresos_por_tienda_' . date('Y-m-d') . '.csv"',
        ]);
    }

    /** Pedidos → CSV */
    public function pedidosCsv(Request $request)
    {
        $estado = $request->input('estado', 'todos');

        $query = Pedido::with('user:id,name,email')->select(['id', 'user_id', 'estado', 'total', 'created_at']);
        if ($estado !== 'todos') {
            $query->where('estado', $estado);
        }
        $pedidos = $query->orderByDesc('created_at')->get();

        $lines = ["ID,Cliente,Email,Estado,Total (€),Fecha"];
        foreach ($pedidos as $p) {
            $nombre = '"' . str_replace('"', '""', $p->user?->name ?? 'Desconocido') . '"';
            $email  = '"' . str_replace('"', '""', $p->user?->email ?? '') . '"';
            $lines[] = "#{$p->id},{$nombre},{$email},{$p->estado}," . number_format((float) $p->total, 2, '.', '') . ',' . $p->created_at->format('d/m/Y H:i');
        }

        return response(implode("\n", $lines), 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="pedidos_' . date('Y-m-d') . '.csv"',
        ]);
    }
}
