<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Tienda;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class BusquedaController extends Controller
{
    /**
     * Página de resultados de búsqueda.
     *
     * Estrategia de matching (de más estricto a más laxo):
     *  1) Match completo: todas las palabras de la query están presentes
     *     (AND entre tokens, OR entre columnas).
     *  2) Match parcial: si no hay resultados, repetimos buscando con cada
     *     token aislado (OR entre tokens) por si solo coincide uno.
     *  3) Match aproximado: para queries de una sola palabra >= 5 chars,
     *     intentamos truncarla a 60% para captar variaciones de escritura
     *     ("vinotec" → "vinote" → "vinot"…).
     *
     * En cualquier caso devolvemos también `sugerenciasTiendas` + `categorias`
     * para que el usuario nunca se quede en pantalla vacía.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return redirect('/');
        }

        $tokens = $this->tokenize($q);

        // 1) Match completo (todos los tokens presentes en alguna columna).
        $tiendas   = $this->buscarTiendas($tokens, /*todasLasPalabras*/ true);
        $productos = $this->buscarProductos($tokens, /*todasLasPalabras*/ true);

        // 2) Match parcial: si la búsqueda multi-palabra no dio nada, probamos
        //    a buscar cada palabra por separado.
        if (count($tokens) > 1 && $tiendas->isEmpty() && $productos->isEmpty()) {
            $tiendas   = $this->buscarTiendas($tokens, /*todasLasPalabras*/ false);
            $productos = $this->buscarProductos($tokens, /*todasLasPalabras*/ false);
        }

        // 3) Match aproximado: una sola palabra y aún sin resultados → probar prefijos.
        if ($tiendas->isEmpty() && $productos->isEmpty() && count($tokens) === 1) {
            $palabra = $tokens[0];
            $longitud = mb_strlen($palabra);
            // Probar con prefijos progresivamente más cortos (hasta 3 caracteres)
            for ($corte = (int) floor($longitud * 0.6); $corte >= 3; $corte--) {
                $prefijo = mb_substr($palabra, 0, $corte);
                if ($prefijo === $palabra) continue;

                $tiendas   = $this->buscarTiendas([$prefijo], true);
                $productos = $this->buscarProductos([$prefijo], true);
                if ($tiendas->isNotEmpty() || $productos->isNotEmpty()) break;
            }
        }

        // Sugerencias (siempre): tiendas top y categorías, para el panel inferior.
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

        return Inertia::render('Buscar', [
            'q'                  => $q,
            'tiendas'            => $tiendas,
            'productos'          => $productos,
            'sugerenciasTiendas' => $sugerenciasTiendas,
            'categorias'         => $categorias,
        ]);
    }

    /**
     * Convierte la query en un array de palabras útiles (>= 2 chars).
     * Quita acentos y minimiza para que el match sea más permisivo.
     * (MySQL con utf8mb4_unicode_ci ya es insensible a tildes, pero
     *  pasamos las versiones sin acento como red de seguridad.)
     */
    private function tokenize(string $q): array
    {
        $normalizada = mb_strtolower($q);
        $palabras    = preg_split('/\s+/', $normalizada, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $palabras    = array_filter($palabras, fn ($p) => mb_strlen($p) >= 2);

        // Si la query es una sola palabra muy larga sin espacios, igualmente la dejamos.
        return array_values(array_unique($palabras));
    }

    private function buscarTiendas(array $tokens, bool $todasLasPalabras): Collection
    {
        if (empty($tokens)) return collect();

        return Tienda::query()
            ->with(['categoria', 'user'])
            ->withCount(['productos as productos_count' => fn ($qq) => $qq->where('disponible', true)])
            ->where('visible', true)
            ->where('activa', true)
            ->where(function (Builder $w) use ($tokens, $todasLasPalabras) {
                $aplicarToken = function (Builder $sub, string $token) {
                    $like = '%' . $token . '%';
                    $sub->where(function (Builder $col) use ($like) {
                        $col->where('nombre', 'like', $like)
                            ->orWhere('descripcion', 'like', $like)
                            ->orWhere('direccion', 'like', $like)
                            ->orWhereHas('categoria', fn ($c) => $c->where('nombre', 'like', $like));
                    });
                };

                if ($todasLasPalabras) {
                    foreach ($tokens as $tok) {
                        $w->where(fn (Builder $sub) => $aplicarToken($sub, $tok));
                    }
                } else {
                    $w->where(function (Builder $or) use ($tokens, $aplicarToken) {
                        foreach ($tokens as $tok) {
                            $or->orWhere(fn (Builder $sub) => $aplicarToken($sub, $tok));
                        }
                    });
                }
            })
            ->orderByDesc('valoracion')
            ->orderByDesc('total_resenas')
            ->limit(24)
            ->get();
    }

    private function buscarProductos(array $tokens, bool $todasLasPalabras): Collection
    {
        if (empty($tokens)) return collect();

        return Producto::query()
            ->with(['tienda:id,slug,nombre,logo,imagen_portada', 'categoria:id,slug,nombre'])
            ->where('disponible', true)
            ->whereHas('tienda', fn ($t) => $t->where('visible', true)->where('activa', true))
            ->where(function (Builder $w) use ($tokens, $todasLasPalabras) {
                $aplicarToken = function (Builder $sub, string $token) {
                    $like = '%' . $token . '%';
                    $sub->where(function (Builder $col) use ($like) {
                        $col->where('nombre', 'like', $like)
                            ->orWhere('descripcion', 'like', $like);
                    });
                };

                if ($todasLasPalabras) {
                    foreach ($tokens as $tok) {
                        $w->where(fn (Builder $sub) => $aplicarToken($sub, $tok));
                    }
                } else {
                    $w->where(function (Builder $or) use ($tokens, $aplicarToken) {
                        foreach ($tokens as $tok) {
                            $or->orWhere(fn (Builder $sub) => $aplicarToken($sub, $tok));
                        }
                    });
                }
            })
            ->orderByDesc('destacado')
            ->limit(24)
            ->get();
    }
}
