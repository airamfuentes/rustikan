<?php

namespace App\Http\Controllers;

use App\Models\Tienda;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FavoritoController extends Controller
{
    /**
     * Listado de tiendas favoritas del usuario autenticado.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $tiendas = $user->tiendasFavoritas()
            ->with(['categoria', 'user'])
            ->withCount(['productos as productos_count' => function ($q) {
                $q->where('disponible', true);
            }])
            ->where('visible', true)
            ->where('activa', true)
            ->orderByDesc('tienda_favoritas.created_at')
            ->get();

        return Inertia::render('Profile/Favoritos', [
            'tiendas' => $tiendas,
        ]);
    }

    /**
     * Alterna la tienda favorita del usuario autenticado.
     * Endpoint XHR — responde JSON con el estado actualizado.
     */
    public function toggle(Request $request, Tienda $tienda)
    {
        $user = $request->user();
        $result = $user->tiendasFavoritas()->toggle($tienda->id);

        $favorited = !empty($result['attached']);

        return response()->json([
            'favorited' => $favorited,
            'tienda_id' => $tienda->id,
            'nombre'    => $tienda->nombre,
        ]);
    }
}
