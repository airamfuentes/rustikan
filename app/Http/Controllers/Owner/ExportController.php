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

    private function csvResponse(string $filename, array $lines): \Illuminate\Http\Response
    {
        $bom = "\xEF\xBB\xBF";
        return response($bom . implode("\r\n", $lines), 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-store',
        ]);
    }

    private function num(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }

    private function cell(string $value): string
    {
        return '"' . str_replace('"', '""', $value) . '"';
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

        $lines   = [];
        $lines[] = $this->cell('INFORME DE BENEFICIOS — ' . strtoupper($tienda->nombre));
        $lines[] = 'Generado: ' . now()->format('d/m/Y H:i') . ';Comisión plataforma: ' . $comisionPct . '%';
        $lines[] = '';
        $lines[] = "Mes;Ingresos Brutos (€);Comisión {$comisionPct}% (€);Beneficio Neto (€);Nº Pedidos";

        $totalBruto = 0; $totalPedidos = 0;
        foreach ($rows as $r) {
            $bruto    = (float) $r->bruto;
            $comision = round($bruto * $comisionPct / 100, 2);
            $neto     = round($bruto - $comision, 2);
            $totalBruto   += $bruto;
            $totalPedidos += (int) $r->pedidos;
            $lines[] = "{$r->mes};{$this->num($bruto)};{$this->num($comision)};{$this->num($neto)};{$r->pedidos}";
        }

        $lines[] = '';
        $totalCom = round($totalBruto * $comisionPct / 100, 2);
        $totalNet = round($totalBruto - $totalCom, 2);
        $lines[] = "TOTAL;{$this->num($totalBruto)};{$this->num($totalCom)};{$this->num($totalNet)};{$totalPedidos}";

        return $this->csvResponse('beneficios_' . date('Y-m-d') . '.csv', $lines);
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

        $lines   = [];
        $lines[] = $this->cell('PEDIDOS — ' . strtoupper($tienda->nombre));
        $lines[] = 'Generado: ' . now()->format('d/m/Y H:i') . ';Total registros: ' . $items->count();
        $lines[] = '';
        $lines[] = 'Nº Pedido;Producto;Cliente;Estado;Cantidad;Precio Unitario (€);Subtotal (€);Fecha';

        $totalGeneral = 0;
        foreach ($items as $item) {
            $num = $item->pedido ? ($item->pedido->numero_pedido ?? ('#' . $item->pedido_id)) : ('#' . $item->pedido_id);
            $totalGeneral += (float) $item->subtotal;
            $lines[] = $this->cell($num) . ';'
                . $this->cell($item->producto_nombre ?? ($item->producto?->nombre ?? '')) . ';'
                . $this->cell($item->pedido?->user?->name ?? 'Desconocido') . ';'
                . $this->cell(ucfirst($item->pedido?->estado ?? '')) . ';'
                . $item->cantidad . ';'
                . $this->num((float) $item->precio_unitario) . ';'
                . $this->num((float) $item->subtotal) . ';'
                . $item->created_at->format('d/m/Y H:i');
        }

        $lines[] = '';
        $lines[] = ';;TOTAL VENDIDO;;;;' . $this->num($totalGeneral) . ';';

        return $this->csvResponse('mis_pedidos_' . date('Y-m-d') . '.csv', $lines);
    }
}
