<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Tienda;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with(['tienda:id,nombre,slug', 'categoria:id,nombre'])
            ->orderBy('stock');

        if ($request->filled('tienda_id')) {
            $query->where('tienda_id', $request->tienda_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nombre', 'like', "%{$search}%");
        }

        if ($request->boolean('bajo_stock')) {
            $query->whereColumn('stock', '<=', 'stock_minimo');
        }

        if ($request->boolean('sin_stock')) {
            $query->where('stock', 0);
        }

        $productos = $query->paginate(30)->withQueryString();

        $tiendas = Tienda::where('activa', true)->get(['id', 'nombre']);

        $stats = [
            'total'       => Producto::count(),
            'bajo_stock'  => Producto::whereColumn('stock', '<=', 'stock_minimo')->where('stock', '>', 0)->count(),
            'sin_stock'   => Producto::where('stock', 0)->count(),
            'disponibles' => Producto::where('disponible', true)->count(),
        ];

        return Inertia::render('Supplier/Stock', [
            'productos' => $productos,
            'tiendas'   => $tiendas,
            'stats'     => $stats,
            'filters'   => $request->only(['tienda_id', 'search', 'bajo_stock', 'sin_stock']),
        ]);
    }
}
