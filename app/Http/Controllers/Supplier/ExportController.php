<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    /**
     * Listado de pedidos activos del almacén → vista PDF imprimible.
     */
    public function pedidosPdf(Request $request)
    {
        $estado = $request->input('estado', 'todos');

        $query = Pedido::with(['user:id,name,email', 'items.producto', 'items.tienda'])
            ->whereNotIn('estado', ['cancelado'])
            ->orderByDesc('created_at');

        if ($estado !== 'todos') {
            $query->where('estado', $estado);
        }

        $pedidos = $query->get();

        return view('pdfs.supplier.pedidos', [
            'pedidos' => $pedidos,
            'estado'  => $estado,
        ]);
    }

    /**
     * Historial completo (incluye entregados y cancelados) → vista PDF.
     */
    public function historialPdf(Request $request)
    {
        $query = Pedido::with(['user:id,name,email', 'items.producto', 'items.tienda'])
            ->orderByDesc('created_at');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $pedidos = $query->get();

        return view('pdfs.supplier.historial', [
            'pedidos'     => $pedidos,
            'estado'      => $request->input('estado'),
            'fecha_desde' => $request->input('fecha_desde'),
            'fecha_hasta' => $request->input('fecha_hasta'),
        ]);
    }

    /**
     * Hoja de preparación del pedido para el almacén → vista PDF.
     * Pensada para imprimir y acompañar al pedido durante el preparado.
     */
    public function pedidoPdf(Pedido $pedido)
    {
        $pedido->load(['user', 'items.producto', 'items.tienda']);

        return view('pdfs.supplier.pedido-detalle', [
            'pedido' => $pedido,
        ]);
    }
}
