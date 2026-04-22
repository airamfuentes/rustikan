<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductoController extends Controller
{
    public function edit(Producto $producto)
    {
        $tienda = auth()->user()->tiendas()->firstOrFail();

        if ($producto->tienda_id !== $tienda->id) {
            abort(403);
        }

        $categorias = Categoria::all(['id', 'nombre', 'icono']);

        return Inertia::render('Owner/Producto/Editar', [
            'producto'   => $producto->load('categoria'),
            'categorias' => $categorias,
            'tienda'     => $tienda,
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        $tienda = auth()->user()->tiendas()->firstOrFail();

        if ($producto->tienda_id !== $tienda->id) {
            abort(403);
        }

        $validated = $request->validate([
            'nombre'        => 'required|string|max:255',
            'categoria_id'  => 'required|exists:categorias,id',
            'descripcion'   => 'nullable|string|max:3000',
            'precio'        => 'required|numeric|min:0|max:99999',
            'precio_oferta' => 'nullable|numeric|min:0|lt:precio',
            'unidad'        => 'required|string|max:30',
            'stock'         => 'required|integer|min:0',
            'stock_minimo'  => 'required|integer|min:0',
            'disponible'    => 'boolean',
            'destacado'     => 'boolean',
            // Imagen: archivo
            'imagen'        => 'nullable|image|max:3072',
            // Imagen: URL
            'imagen_url'    => 'nullable|url|max:2048',
            'delete_imagen' => 'nullable|boolean',
        ]);

        // Borrar imagen
        if ($request->boolean('delete_imagen') && $producto->imagen && !str_starts_with($producto->imagen, 'http')) {
            Storage::disk('public')->delete($producto->imagen);
            $validated['imagen'] = null;
        }

        // Subir archivo
        if ($request->hasFile('imagen')) {
            if ($producto->imagen && !str_starts_with($producto->imagen, 'http')) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        } elseif ($request->filled('imagen_url')) {
            $validated['imagen'] = $this->storeFromUrl($request->imagen_url);
        }

        // Regenerar slug si cambia el nombre
        $validated['slug'] = Str::slug($validated['nombre']);

        unset($validated['imagen_url'], $validated['delete_imagen']);

        // Nullable fixes
        if (empty($validated['precio_oferta'])) {
            $validated['precio_oferta'] = null;
        }

        $producto->update($validated);

        return redirect()->route('owner.panel')
            ->with('success', "«{$producto->nombre}» actualizado correctamente.");
    }

    public function toggleOferta(Producto $producto)
    {
        $tienda = auth()->user()->tiendas()->firstOrFail();

        if ($producto->tienda_id !== $tienda->id) {
            abort(403);
        }

        $producto->update(['oferta_activa' => !$producto->oferta_activa]);

        return back()->with('success', $producto->oferta_activa
            ? "Oferta activada para «{$producto->nombre}»."
            : "Oferta desactivada para «{$producto->nombre}».");
    }

    private function storeFromUrl(string $url): ?string
    {
        try {
            $contents = @file_get_contents($url);
            if ($contents === false) return null;
            $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) $ext = 'jpg';
            $filename = 'productos/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($filename, $contents);
            return $filename;
        } catch (\Throwable) {
            return null;
        }
    }
}
