<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TiendaController extends Controller
{
    public function edit()
    {
        $tienda     = auth()->user()->tiendas()->firstOrFail();
        $categorias = Categoria::all(['id', 'nombre', 'icono']);

        return Inertia::render('Owner/Tienda/Editar', [
            'tienda'     => $tienda,
            'categorias' => $categorias,
        ]);
    }

    public function update(Request $request)
    {
        $tienda = auth()->user()->tiendas()->firstOrFail();

        $validated = $request->validate([
            'nombre'              => 'required|string|max:255',
            'categoria_id'        => 'required|exists:categorias,id',
            'descripcion'         => 'nullable|string|max:5000',
            'telefono'            => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:255',
            'direccion'           => 'nullable|string|max:500',
            // Imagen: fichero
            'logo'                => 'nullable|image|max:3072',
            'imagen_portada'      => 'nullable|image|max:3072',
            // Imagen: URL externa
            'logo_url'            => 'nullable|url|max:2048',
            'imagen_portada_url'  => 'nullable|url|max:2048',
            // Borrar imágenes
            'delete_logo'           => 'nullable|boolean',
            'delete_imagen_portada' => 'nullable|boolean',
        ]);

        // Borrar imágenes si se solicita
        if ($request->boolean('delete_logo') && $tienda->logo && !str_starts_with($tienda->logo, 'http')) {
            Storage::disk('public')->delete($tienda->logo);
            $validated['logo'] = null;
        }

        if ($request->boolean('delete_imagen_portada') && $tienda->imagen_portada && !str_starts_with($tienda->imagen_portada, 'http')) {
            Storage::disk('public')->delete($tienda->imagen_portada);
            $validated['imagen_portada'] = null;
        }

        // Logo: archivo tiene prioridad sobre URL
        if ($request->hasFile('logo')) {
            if ($tienda->logo && !str_starts_with($tienda->logo, 'http')) {
                Storage::disk('public')->delete($tienda->logo);
            }
            $validated['logo'] = $request->file('logo')->store('tiendas/logos', 'public');
        } elseif ($request->filled('logo_url')) {
            $validated['logo'] = $this->storeFromUrl($request->logo_url, 'tiendas/logos');
        }

        // Portada: archivo tiene prioridad sobre URL
        if ($request->hasFile('imagen_portada')) {
            if ($tienda->imagen_portada && !str_starts_with($tienda->imagen_portada, 'http')) {
                Storage::disk('public')->delete($tienda->imagen_portada);
            }
            $validated['imagen_portada'] = $request->file('imagen_portada')->store('tiendas/portadas', 'public');
        } elseif ($request->filled('imagen_portada_url')) {
            $validated['imagen_portada'] = $this->storeFromUrl($request->imagen_portada_url, 'tiendas/portadas');
        }

        // Actualizar slug si cambia el nombre
        $validated['slug'] = Str::slug($validated['nombre']);

        unset($validated['delete_logo'], $validated['delete_imagen_portada'], $validated['logo_url'], $validated['imagen_portada_url']);

        $tienda->update($validated);

        return redirect()->route('owner.panel')
            ->with('success', 'Tienda actualizada correctamente.');
    }

    // ── Helper: descarga imagen de una URL y la guarda en storage ─────────────
    private function storeFromUrl(string $url, string $folder): ?string
    {
        try {
            $contents = @file_get_contents($url);
            if ($contents === false) {
                return null;
            }
            // Intentar detectar extensión a partir de la URL o cabecera
            $path    = parse_url($url, PHP_URL_PATH);
            $ext     = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowed)) {
                $ext = 'jpg';
            }
            $filename = $folder . '/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($filename, $contents);
            return $filename;
        } catch (\Throwable) {
            return null;
        }
    }
}
