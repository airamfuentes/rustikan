<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tienda;
use App\Models\Categoria;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TiendaController extends Controller
{
    // ── Helper: descargar imagen desde URL y guardarla ───────────────────────
    private function storeFromUrl(string $url, string $folder): ?string
    {
        try {
            $contents = @file_get_contents($url);
            if ($contents === false) return null;
            $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) $ext = 'jpg';
            $filename = $folder . '/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($filename, $contents);
            return $filename;
        } catch (\Throwable) {
            return null;
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Tienda::with(['user', 'categoria'])
            ->withCount('productos');

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('direccion', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por categoría
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // Filtro por estado activa
        if ($request->filled('activa')) {
            $query->where('activa', $request->activa === '1');
        }

        // Filtro por visible
        if ($request->filled('visible')) {
            $query->where('visible', $request->visible === '1');
        }

        // Ordenar
        $tiendas = $query->latest()->paginate(20)->withQueryString();

        // Estadísticas
        $stats = [
            'total' => Tienda::count(),
            'activas' => Tienda::where('activa', true)->count(),
            'inactivas' => Tienda::where('activa', false)->count(),
            'visibles' => Tienda::where('visible', true)->count(),
            'ocultas' => Tienda::where('visible', false)->count(),
        ];

        // Categorías para el filtro
        $categorias = Categoria::all(['id', 'nombre', 'icono']);

        return Inertia::render('Admin/Tiendas/Index', [
            'tiendas' => $tiendas,
            'stats' => $stats,
            'categorias' => $categorias,
            'filters' => $request->only(['search', 'categoria_id', 'activa', 'visible']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::all();
        $usuarios = User::all(['id', 'name', 'email']);
        
        return Inertia::render('Admin/Tiendas/Crear', [
            'categorias' => $categorias,
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'logo' => 'nullable|image|max:2048',
            'imagen_portada' => 'nullable|image|max:2048',
            'logo_url' => 'nullable|url|max:2048',
            'imagen_portada_url' => 'nullable|url|max:2048',
        ]);

        $validated['slug'] = Str::slug($validated['nombre']);

        // Manejar subida de logo
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('tiendas/logos', 'public');
        } elseif ($request->filled('logo_url')) {
            $validated['logo'] = $this->storeFromUrl($request->logo_url, 'tiendas/logos');
        } else {
            unset($validated['logo']);
        }

        // Manejar subida de imagen de portada
        if ($request->hasFile('imagen_portada')) {
            $validated['imagen_portada'] = $request->file('imagen_portada')->store('tiendas/portadas', 'public');
        } elseif ($request->filled('imagen_portada_url')) {
            $validated['imagen_portada'] = $this->storeFromUrl($request->imagen_portada_url, 'tiendas/portadas');
        } else {
            unset($validated['imagen_portada']);
        }

        unset($validated['logo_url'], $validated['imagen_portada_url']);

        $tienda = Tienda::create($validated);

        // Registrar actividad
        ActivityLog::log(
            'nueva_tienda',
            "Nueva tienda creada: {$tienda->nombre}",
            '🏪',
            'blue',
            $tienda
        );

        return redirect()->route('admin.tiendas.index')
            ->with('success', 'Tienda creada exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tienda $tienda)
    {
        $tienda->load(['user', 'categoria', 'productos']);
        
        return Inertia::render('Admin/Tiendas/Detalle', [
            'tienda' => $tienda,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tienda $tienda)
    {
        $categorias = Categoria::all();
        
        return Inertia::render('Admin/Tiendas/Editar', [
            'tienda' => $tienda,
            'categorias' => $categorias,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tienda $tienda)
    {
        $validated = $request->validate([
            'categoria_id'          => 'sometimes|exists:categorias,id',
            'nombre'                => 'sometimes|string|max:255',
            'descripcion'           => 'nullable|string',
            'telefono'              => 'nullable|string|max:20',
            'email'                 => 'nullable|email|max:255',
            'direccion'             => 'nullable|string',
            'latitud'               => 'nullable|numeric|between:-90,90',
            'longitud'              => 'nullable|numeric|between:-180,180',
            'logo'                  => 'nullable|image|max:2048',
            'imagen_portada'        => 'nullable|image|max:2048',
            'logo_url'              => 'nullable|url|max:2048',
            'imagen_portada_url'    => 'nullable|url|max:2048',
            'delete_logo'           => 'nullable|boolean',
            'delete_imagen_portada' => 'nullable|boolean',
            'activa'                => 'sometimes|boolean',
            'visible'               => 'sometimes|boolean',
        ]);

        if (isset($validated['nombre'])) {
            $validated['slug'] = Str::slug($validated['nombre']);
        }

        // Eliminar logo si se solicitó
        if ($request->boolean('delete_logo') && $tienda->logo) {
            if (!str_starts_with($tienda->logo, 'http')) Storage::disk('public')->delete($tienda->logo);
            $validated['logo'] = null;
        }

        // Eliminar portada si se solicitó
        if ($request->boolean('delete_imagen_portada') && $tienda->imagen_portada) {
            if (!str_starts_with($tienda->imagen_portada, 'http')) Storage::disk('public')->delete($tienda->imagen_portada);
            $validated['imagen_portada'] = null;
        }

        // Subir nuevo logo
        if ($request->hasFile('logo')) {
            if ($tienda->logo && !str_starts_with($tienda->logo, 'http')) {
                Storage::disk('public')->delete($tienda->logo);
            }
            $validated['logo'] = $request->file('logo')->store('tiendas/logos', 'public');
        } elseif ($request->filled('logo_url')) {
            $validated['logo'] = $this->storeFromUrl($request->logo_url, 'tiendas/logos');
        }

        // Subir nueva portada
        if ($request->hasFile('imagen_portada')) {
            if ($tienda->imagen_portada && !str_starts_with($tienda->imagen_portada, 'http')) {
                Storage::disk('public')->delete($tienda->imagen_portada);
            }
            $validated['imagen_portada'] = $request->file('imagen_portada')->store('tiendas/portadas', 'public');
        } elseif ($request->filled('imagen_portada_url')) {
            $validated['imagen_portada'] = $this->storeFromUrl($request->imagen_portada_url, 'tiendas/portadas');
        }

        // Limpiar flags auxiliares antes de guardar
        unset($validated['delete_logo'], $validated['delete_imagen_portada'], $validated['logo_url'], $validated['imagen_portada_url']);

        $tienda->update($validated);

        ActivityLog::log(
            'actualizar_tienda',
            "Tienda actualizada: {$tienda->nombre}",
            '✏️',
            'yellow',
            $tienda,
            $validated
        );

        return back()->with('success', 'Tienda actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tienda $tienda)
    {
        $nombre = $tienda->nombre;
        $tienda->delete();

        // Registrar actividad
        ActivityLog::log(
            'eliminar_tienda',
            "Tienda eliminada: {$nombre}",
            '🗑️',
            'red'
        );

        return redirect()->route('admin.tiendas.index')
            ->with('success', 'Tienda eliminada exitosamente');
    }

    /**
     * Toggle tienda visibility
     */
    public function toggleVisible(Tienda $tienda)
    {
        $tienda->update(['visible' => !$tienda->visible]);
        
        // Registrar actividad
        $estado = $tienda->visible ? 'visible' : 'oculta';
        ActivityLog::log(
            'cambiar_visibilidad_tienda',
            "Tienda cambió visibilidad: {$tienda->nombre} ahora {$estado}",
            '👁️',
            'purple',
            $tienda
        );
        
        return back()->with('success', 'Visibilidad actualizada');
    }

    /**
     * Toggle tienda active status
     */
    public function toggleActive(Tienda $tienda)
    {
        $tienda->update(['activa' => !$tienda->activa]);
        
        // Registrar actividad
        $estado = $tienda->activa ? 'activada' : 'desactivada';
        ActivityLog::log(
            'cambiar_estado_tienda',
            "Tienda {$estado}: {$tienda->nombre}",
            $tienda->activa ? '✅' : '❌',
            $tienda->activa ? 'green' : 'orange',
            $tienda
        );
        
        return back()->with('success', 'Estado actualizado');
    }
}
