<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\PedidoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    private function tienda()
    {
        return auth()->user()->tiendas()->first();
    }

    /** Beneficios mensuales del propietario → PDF imprimible */
    public function beneficiosPdf(Request $request)
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

        return view('pdfs.owner.beneficios', [
            'tienda'      => $tienda,
            'rows'        => $rows,
            'comisionPct' => $comisionPct,
        ]);
    }

    /** Pedidos del propietario → PDF imprimible */
    public function pedidosPdf(Request $request)
    {
        $tienda = $this->tienda();
        if (!$tienda) abort(403);

        $items = PedidoItem::with(['pedido.user:id,name,email', 'producto:id,nombre'])
            ->where('tienda_id', $tienda->id)
            ->orderByDesc('created_at')
            ->get();

        return view('pdfs.owner.pedidos', [
            'tienda' => $tienda,
            'items'  => $items,
        ]);
    }
}
