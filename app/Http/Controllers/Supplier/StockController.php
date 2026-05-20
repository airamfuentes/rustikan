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
        $query = Tienda::withCount([
            'productos as total_productos',
            'productos as sin_stock'    => fn($q) => $q->where('stock', 0),
            'productos as bajo_stock'   => fn($q) => $q->whereColumn('stock', '<=', 'stock_minimo')->where('stock', '>', 0),
        ])
        ->where('activa', true)
        ->orderBy('nombre');

        if ($request->filled('search')) {
            $query->where('nombre', 'like', "%{$request->search}%");
        }

        $tiendas = $query->paginate(10)->withQueryString();

        $stats = [
            'total'       => Producto::count(),
            'bajo_stock'  => Producto::whereColumn('stock', '<=', 'stock_minimo')->where('stock', '>', 0)->count(),
            'sin_stock'   => Producto::where('stock', 0)->count(),
            'disponibles' => Producto::where('disponible', true)->count(),
        ];

        return Inertia::render('Supplier/Stock', [
            'tiendas' => $tiendas,
            'stats'   => $stats,
            'filters' => $request->only(['search']),
        ]);
    }

    public function tienda(Request $request, Tienda $tienda)
    {
        $query = Producto::with(['categoria:id,nombre'])
            ->where('tienda_id', $tienda->id)
            ->orderBy('stock');

        if ($request->filled('search')) {
            $query->where('nombre', 'like', "%{$request->search}%");
        }

        if ($request->boolean('bajo_stock')) {
            $query->whereColumn('stock', '<=', 'stock_minimo');
        }

        if ($request->boolean('sin_stock')) {
            $query->where('stock', 0);
        }

        $productos = $query->paginate(30)->withQueryString();

        $stats = [
            'total'       => Producto::where('tienda_id', $tienda->id)->count(),
            'bajo_stock'  => Producto::where('tienda_id', $tienda->id)->whereColumn('stock', '<=', 'stock_minimo')->where('stock', '>', 0)->count(),
            'sin_stock'   => Producto::where('tienda_id', $tienda->id)->where('stock', 0)->count(),
            'disponibles' => Producto::where('tienda_id', $tienda->id)->where('disponible', true)->count(),
        ];

        return Inertia::render('Supplier/StockTienda', [
            'tienda'   => $tienda->only('id', 'nombre', 'slug', 'logo'),
            'productos' => $productos,
            'stats'    => $stats,
            'filters'  => $request->only(['search', 'bajo_stock', 'sin_stock']),
        ]);
    }
}
