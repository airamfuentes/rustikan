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
        $supplierId = auth()->id();

        $query = Tienda::withCount([
            'productos as total_productos',
            'productos as sin_stock'  => fn($q) => $q->where('stock', 0),
            'productos as bajo_stock' => fn($q) => $q->whereColumn('stock', '<=', 'stock_minimo')->where('stock', '>', 0),
        ])
        ->where('user_id', $supplierId)
        ->where('activa', true);

        if ($request->filled('search')) {
            $query->where('nombre', 'like', "%{$request->search}%");
        }

        if ($request->boolean('con_bajo_stock')) {
            $query->has('productos', '>=', 1, 'and', fn($q) =>
                $q->whereColumn('stock', '<=', 'stock_minimo')->where('stock', '>', 0)
            );
        }

        if ($request->boolean('con_sin_stock')) {
            $query->has('productos', '>=', 1, 'and', fn($q) => $q->where('stock', 0));
        }

        // Ordenar: más problemáticas primero por defecto, o por nombre
        $orden = $request->input('orden', 'problemas');
        if ($orden === 'nombre') {
            $query->orderBy('nombre');
        } else {
            $query->orderByRaw('(sin_stock + bajo_stock) desc')->orderBy('nombre');
        }

        $tiendas = $query->paginate(10)->withQueryString();

        // Stats solo de las tiendas del supplier
        $tiendaIds = Tienda::where('user_id', $supplierId)->pluck('id');

        $stats = [
            'total'       => Producto::whereIn('tienda_id', $tiendaIds)->count(),
            'bajo_stock'  => Producto::whereIn('tienda_id', $tiendaIds)->whereColumn('stock', '<=', 'stock_minimo')->where('stock', '>', 0)->count(),
            'sin_stock'   => Producto::whereIn('tienda_id', $tiendaIds)->where('stock', 0)->count(),
            'disponibles' => Producto::whereIn('tienda_id', $tiendaIds)->where('disponible', true)->count(),
        ];

        return Inertia::render('Supplier/Stock', [
            'tiendas' => $tiendas,
            'stats'   => $stats,
            'filters' => $request->only(['search', 'con_bajo_stock', 'con_sin_stock', 'orden']),
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
