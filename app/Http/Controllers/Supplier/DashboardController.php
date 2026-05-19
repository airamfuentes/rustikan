<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Producto;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'pendientes'      => Pedido::where('estado', 'pendiente')->count(),
            'confirmados'     => Pedido::where('estado', 'confirmado')->count(),
            'en_preparacion'  => Pedido::where('estado', 'en_preparacion')->count(),
            'enviados'        => Pedido::where('estado', 'enviado')->count(),
            'incidencias'     => Pedido::where('estado', 'incidencia')->count(),
        ];

        $pedidos_recientes = Pedido::with(['user:id,name,email', 'items'])
            ->whereIn('estado', ['pendiente', 'confirmado', 'en_preparacion'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn ($p) => [
                'id'             => $p->id,
                'numero_pedido'  => $p->numero_pedido,
                'estado'         => $p->estado,
                'total'          => $p->total,
                'cliente'        => $p->user?->name ?? '—',
                'items_count'    => $p->items->count(),
                'created_at'     => $p->created_at,
            ]);

        $bajo_stock = Producto::with('tienda:id,nombre')
            ->where('disponible', true)
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('stock')
            ->limit(6)
            ->get(['id', 'nombre', 'stock', 'stock_minimo', 'tienda_id']);

        return Inertia::render('Supplier/Dashboard', [
            'stats'           => $stats,
            'pedidos_recientes' => $pedidos_recientes,
            'bajo_stock'      => $bajo_stock,
        ]);
    }
}
