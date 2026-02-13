<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Producto::with(['tienda.categoria']);

        // Búsqueda por nombre de producto o tienda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhereHas('tienda', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by low stock
        if ($request->filled('bajo_stock') && $request->bajo_stock) {
            $query->whereRaw('stock <= stock_minimo');
        }

        // Filter by disponible
        if ($request->filled('disponible') && $request->disponible !== '') {
            $query->where('disponible', $request->disponible == '1');
        }

        // Ordenar
        $productos = $query->latest()->paginate(20)->withQueryString();

        // Estadísticas
        $stats = [
            'total' => Producto::count(),
            'disponibles' => Producto::where('disponible', true)->count(),
            'no_disponibles' => Producto::where('disponible', false)->count(),
            'bajo_stock' => Producto::whereRaw('stock <= stock_minimo')->count(),
            'destacados' => Producto::where('destacado', true)->count(),
        ];

        return Inertia::render('Admin/Productos/Index', [
            'productos' => $productos,
            'stats' => $stats,
            'filters' => $request->only(['search', 'bajo_stock', 'disponible']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tiendas = Tienda::where('activa', true)->get();
        
        return Inertia::render('Admin/Productos/Create', [
            'tiendas' => $tiendas,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tienda_id' => 'required|exists:tiendas,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'precio_oferta' => 'nullable|numeric|min:0',
            'unidad' => 'required|string|max:50',
            'imagen' => 'nullable|string',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'destacado' => 'sometimes|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['nombre']);

        $producto = Producto::create($validated);
        $producto->load('tienda');

        // Registrar actividad
        ActivityLog::log(
            'nuevo_producto',
            "Nuevo producto creado: {$producto->nombre} en {$producto->tienda->nombre}",
            '📦',
            'purple',
            $producto
        );

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        $producto->load('tienda.categoria');
        
        return Inertia::render('Admin/Productos/Show', [
            'producto' => $producto,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        $tiendas = Tienda::all();
        
        return Inertia::render('Admin/Productos/Edit', [
            'producto' => $producto,
            'tiendas' => $tiendas,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'tienda_id' => 'sometimes|exists:tiendas,id',
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'sometimes|numeric|min:0',
            'precio_oferta' => 'nullable|numeric|min:0',
            'unidad' => 'sometimes|string|max:50',
            'imagen' => 'nullable|string',
            'stock' => 'sometimes|integer|min:0',
            'stock_minimo' => 'sometimes|integer|min:0',
            'disponible' => 'sometimes|boolean',
            'destacado' => 'sometimes|boolean',
        ]);

        if (isset($validated['nombre'])) {
            $validated['slug'] = Str::slug($validated['nombre']);
        }

        $producto->update($validated);

        // Registrar actividad
        $cambios = [];
        if (isset($validated['precio'])) $cambios[] = "precio: {$validated['precio']}€";
        if (isset($validated['stock'])) $cambios[] = "stock: {$validated['stock']}";
        if (isset($validated['disponible'])) $cambios[] = "disponible: " . ($validated['disponible'] ? 'sí' : 'no');
        
        $descripcionCambios = !empty($cambios) ? ' (' . implode(', ', $cambios) . ')' : '';
        
        ActivityLog::log(
            'actualizar_producto',
            "Producto actualizado: {$producto->nombre}{$descripcionCambios}",
            '✏️',
            'yellow',
            $producto,
            $validated
        );

        return back()->with('success', 'Producto actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        $nombre = $producto->nombre;
        $producto->delete();

        // Registrar actividad
        ActivityLog::log(
            'eliminar_producto',
            "Producto eliminado: {$nombre}",
            '🗑️',
            'red'
        );

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto eliminado exitosamente');
    }

    /**
     * Update product stock
     */
    public function updateStock(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $producto->update($validated);

        // Registrar actividad
        ActivityLog::log(
            'actualizar_stock',
            "Stock actualizado: {$producto->nombre} ahora tiene {$validated['stock']} unidades",
            '📊',
            'blue',
            $producto,
            $validated
        );

        return back()->with('success', 'Stock actualizado exitosamente');
    }
}
