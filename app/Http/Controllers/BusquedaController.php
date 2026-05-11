<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Tienda;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BusquedaController extends Controller
{
    /**
     * Página de resultados de búsqueda.
     * Si la query no coincide con nada, se muestran sugerencias para que
     * el usuario nunca se quede en una pantalla vacía.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        // Si la query es demasiado corta, redirigimos al home.
        if (mb_strlen($q) < 2) {
            return redirect('/');
        }

        $like = '%' . $q . '%';

        // Tiendas que coinciden (por nombre, descripción, dirección o nombre de categoría).
        $tiendas = Tienda::query()
            ->with(['categoria', 'user'])
            ->withCount(['productos as productos_count' => fn ($qq) => $qq->where('disponible', true)])
            ->where('visible', true)
            ->where('activa', true)
            ->where(function ($w) use ($like) {
                $w->where('nombre', 'like', $like)
                    ->orWhere('descripcion', 'like', $like)
                    ->orWhere('direccion', 'like', $like)
                    ->orWhereHas('categoria', fn ($c) => $c->where('nombre', 'like', $like));
            })
            ->orderByDesc('valoracion')
            ->limit(24)
            ->get();

        // Productos que coinciden (por nombre o descripción), solo de tiendas activas y visibles.
        $productos = Producto::query()
            ->with(['tienda:id,slug,nombre,logo,imagen_portada', 'categoria:id,slug,nombre'])
            ->where('disponible', true)
            ->whereHas('tienda', fn ($t) => $t->where('visible', true)->where('activa', true))
            ->where(function ($w) use ($like) {
                $w->where('nombre', 'like', $like)
                    ->orWhere('descripcion', 'like', $like);
            })
            ->orderByDesc('destacado')
            ->limit(24)
            ->get();

        // Sugerencias para los casos en los que no hay match: tiendas top y categorías.
        $sugerenciasTiendas = collect();
        $categorias = collect();

        if ($tiendas->isEmpty() && $productos->isEmpty()) {
            $sugerenciasTiendas = Tienda::query()
                ->with(['categoria', 'user'])
                ->withCount(['productos as productos_count' => fn ($qq) => $qq->where('disponible', true)])
                ->where('visible', true)
                ->where('activa', true)
                ->orderByDesc('valoracion')
                ->orderByDesc('total_resenas')
                ->limit(6)
                ->get();

            $categorias = Categoria::query()
                ->withCount(['tiendas as tiendas_count' => function ($qq) {
                    $qq->where('visible', true)->where('activa', true);
                }])
                ->orderByDesc('tiendas_count')
                ->get();
        }

        return Inertia::render('Buscar', [
            'q'                  => $q,
            'tiendas'            => $tiendas,
            'productos'          => $productos,
            'sugerenciasTiendas' => $sugerenciasTiendas,
            'categorias'         => $categorias,
        ]);
    }
}
