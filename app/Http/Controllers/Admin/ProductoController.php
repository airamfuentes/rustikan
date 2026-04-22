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
     * Redirect old index to tiendas list (products are now nested under tienda).
     */
    public function index(Request $request)
    {
        return redirect()->route('admin.tiendas.index');
    }

    /**
     * List products for a specific tienda.
     */
    public function tiendaProductos(Request $request, Tienda $tienda)
    {
        $query = Producto::where('tienda_id', $tienda->id);

        if ($request->filled('search')) {
            $query->where('nombre', 'like', "%{$request->search}%");
        }

        if ($request->filled('disponible') && $request->disponible !== '') {
            $query->where('disponible', $request->disponible == '1');
        }

        $productos = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'         => Producto::where('tienda_id', $tienda->id)->count(),
            'disponibles'   => Producto::where('tienda_id', $tienda->id)->where('disponible', true)->count(),
            'no_disponibles'=> Producto::where('tienda_id', $tienda->id)->where('disponible', false)->count(),
            'destacados'    => Producto::where('tienda_id', $tienda->id)->where('destacado', true)->count(),
        ];

        return Inertia::render('Admin/Productos/Index', [
            'tienda'    => $tienda->load('categoria'),
            'productos' => $productos,
            'stats'     => $stats,
            'filters'   => $request->only(['search', 'disponible']),
        ]);
    }

    /**
     * Show create form locked to a specific tienda.
     */
    public function tiendaCrear(Tienda $tienda)
    {
        return Inertia::render('Admin/Productos/Crear', [
            'tienda' => $tienda->load('categoria'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tiendas = Tienda::where('activa', true)->get();
        
        return Inertia::render('Admin/Productos/Crear', [
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

        return redirect()->route('admin.tiendas.productos.index', $validated['tienda_id'])
            ->with('success', 'Producto creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        $producto->load('tienda.categoria');
        
        return Inertia::render('Admin/Productos/Detalle', [
            'producto' => $producto,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        return Inertia::render('Admin/Productos/Editar', [
            'producto' => $producto,
            'tienda'   => $producto->tienda()->with('categoria')->first(),
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
        $tiendaId = $producto->tienda_id;
        $nombre = $producto->nombre;
        $producto->delete();

        // Registrar actividad
        ActivityLog::log(
            'eliminar_producto',
            "Producto eliminado: {$nombre}",
            '🗑️',
            'red'
        );

        return redirect()->route('admin.tiendas.productos.index', $tiendaId)
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

    /**
     * Toggle offer price activation
     */
    public function toggleOferta(Producto $producto)
    {
        $producto->update(['oferta_activa' => !$producto->oferta_activa]);

        ActivityLog::log(
            'toggle_oferta',
            $producto->oferta_activa
                ? "Oferta activada: {$producto->nombre} ({$producto->precio_oferta}€)"
                : "Oferta desactivada: {$producto->nombre}",
            '🏷️',
            'amber',
            $producto
        );

        return back()->with('success', $producto->oferta_activa
            ? 'Oferta activada correctamente.'
            : 'Oferta desactivada.');
    }
}
