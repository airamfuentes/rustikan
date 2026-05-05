<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    private function tienda()
    {
        return auth()->user()->tiendas()->first();
    }

    /** Beneficios mensuales propietario → CSV */
    public function beneficiosCsv(Request $request)
    {
        $tienda = $this->tienda();
        if (!$tienda) abort(403);

        $comisionPct = 10.0;

        $rows = DB::table('pedido_items')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->select(
                DB::raw('DATE_FORMAT(pedidos.created_at, "%Y-%m") as mes'),
                DB::raw('SUM(pedido_items.subtotal) as bruto'),
                DB::raw('COUNT(DISTINCT pedidos.id) as pedidos')
            )
            ->where('pedido_items.tienda_id', $tienda->id)
            ->where('pedidos.estado', 'entregado')
            ->where('pedidos.created_at', '>=', now()->subMonths(12))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $lines = ["Mes,Ingresos brutos (€),Comisión {$comisionPct}% (€),Neto (€),Pedidos"];
        foreach ($rows as $r) {
            $bruto    = round((float) $r->bruto, 2);
            $comision = round($bruto * $comisionPct / 100, 2);
            $neto     = round($bruto - $comision, 2);
            $lines[]  = "{$r->mes},{$bruto},{$comision},{$neto},{$r->pedidos}";
        }

        return response(implode("\n", $lines), 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="beneficios_' . date('Y-m-d') . '.csv"',
        ]);
    }

    /** Pedidos del propietario → CSV */
    public function pedidosCsv(Request $request)
    {
        $tienda = $this->tienda();
        if (!$tienda) abort(403);

        $items = PedidoItem::with(['pedido.user:id,name,email', 'producto:id,nombre'])
            ->where('tienda_id', $tienda->id)
            ->orderByDesc('created_at')
            ->get();

        $lines = ["ID Pedido,Producto,Cliente,Estado,Cantidad,Precio unitario (€),Subtotal (€),Fecha"];
        foreach ($items as $item) {
            $producto = '"' . str_replace('"', '""', $item->producto_nombre ?? ($item->producto?->nombre ?? '')) . '"';
            $cliente  = '"' . str_replace('"', '""', $item->pedido?->user?->name ?? 'Desconocido') . '"';
            $lines[]  = "#{$item->pedido_id},{$producto},{$cliente},{$item->pedido?->estado},{$item->cantidad},"
                . number_format((float) $item->precio_unitario, 2, '.', '') . ','
                . number_format((float) $item->subtotal, 2, '.', '') . ','
                . $item->created_at->format('d/m/Y H:i');
        }

        return response(implode("\n", $lines), 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="mis_pedidos_' . date('Y-m-d') . '.csv"',
        ]);
    }
}
