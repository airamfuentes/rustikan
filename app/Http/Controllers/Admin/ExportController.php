<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    /** Genera respuesta CSV con BOM UTF-8 (compatible con Excel en español) */
    private function csvResponse(string $filename, array $lines): \Illuminate\Http\Response
    {
        // BOM UTF-8 para que Excel lo reconozca correctamente
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

        $lines   = [];
        $lines[] = $this->cell('INFORME DE INGRESOS MENSUALES — RUSTIKAN');
        $lines[] = $this->cell('Período: ' . $desde . ' a ' . $hasta) . ';Generado: ' . now()->format('d/m/Y H:i');
        $lines[] = '';
        $lines[] = 'Mes;Ingresos Brutos (€);Comisión 10% (€);Beneficio Neto (€);Nº Pedidos';

        $totalBruto = 0; $totalPedidos = 0;
        foreach ($rows as $r) {
            $bruto    = (float) $r->total;
            $comision = round($bruto * 0.10, 2);
            $neto     = round($bruto - $comision, 2);
            $totalBruto    += $bruto;
            $totalPedidos  += (int) $r->pedidos;
            $lines[] = "{$r->mes};{$this->num($bruto)};{$this->num($comision)};{$this->num($neto)};{$r->pedidos}";
        }

        $lines[] = '';
        $totalCom = round($totalBruto * 0.10, 2);
        $totalNet = round($totalBruto - $totalCom, 2);
        $lines[] = "TOTAL;{$this->num($totalBruto)};{$this->num($totalCom)};{$this->num($totalNet)};{$totalPedidos}";

        return $this->csvResponse('ingresos_mensuales_' . date('Y-m-d') . '.csv', $lines);
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

        $lines   = [];
        $lines[] = $this->cell('INGRESOS POR TIENDA — RUSTIKAN');
        $lines[] = $this->cell('Período: ' . $desde . ' a ' . $hasta) . ';Generado: ' . now()->format('d/m/Y H:i');
        $lines[] = '';
        $lines[] = 'Tienda;Ingresos Brutos (€);Comisión 10% (€);Beneficio Neto (€);Nº Pedidos';

        $totalBruto = 0; $totalPedidos = 0;
        foreach ($rows as $r) {
            $bruto    = (float) $r->total_ingresos;
            $comision = round($bruto * 0.10, 2);
            $neto     = round($bruto - $comision, 2);
            $totalBruto   += $bruto;
            $totalPedidos += (int) $r->total_pedidos;
            $lines[] = "{$this->cell($r->nombre)};{$this->num($bruto)};{$this->num($comision)};{$this->num($neto)};{$r->total_pedidos}";
        }

        $lines[] = '';
        $totalCom = round($totalBruto * 0.10, 2);
        $totalNet = round($totalBruto - $totalCom, 2);
        $lines[] = "TOTAL;{$this->num($totalBruto)};{$this->num($totalCom)};{$this->num($totalNet)};{$totalPedidos}";

        return $this->csvResponse('ingresos_por_tienda_' . date('Y-m-d') . '.csv', $lines);
    }

    /** Pedidos → CSV */
    public function pedidosCsv(Request $request)
    {
        $estado = $request->input('estado', 'todos');

        $query = Pedido::with('user:id,name,email')->select(['id', 'numero_pedido', 'user_id', 'estado', 'total', 'created_at', 'direccion_envio']);
        if ($estado !== 'todos') {
            $query->where('estado', $estado);
        }
        $pedidos = $query->orderByDesc('created_at')->get();

        $lines   = [];
        $lines[] = $this->cell('LISTADO DE PEDIDOS — RUSTIKAN');
        $lines[] = 'Generado: ' . now()->format('d/m/Y H:i') . ';Total registros: ' . $pedidos->count();
        $lines[] = '';
        $lines[] = 'Nº Pedido;Cliente;Email;Estado;Total (€);Dirección;Fecha';

        $totalGeneral = 0;
        foreach ($pedidos as $p) {
            $num = $p->numero_pedido ?? ('#' . $p->id);
            $totalGeneral += (float) $p->total;
            $lines[] = $this->cell($num) . ';'
                . $this->cell($p->user?->name ?? 'Desconocido') . ';'
                . $this->cell($p->user?->email ?? '') . ';'
                . $this->cell(ucfirst($p->estado)) . ';'
                . $this->num((float) $p->total) . ';'
                . $this->cell($p->direccion_envio ?? '') . ';'
                . $p->created_at->format('d/m/Y H:i');
        }

        $lines[] = '';
        $lines[] = ';;TOTAL FACTURADO;;' . $this->num($totalGeneral) . ';;';

        return $this->csvResponse('pedidos_' . date('Y-m-d') . '.csv', $lines);
    }
}
