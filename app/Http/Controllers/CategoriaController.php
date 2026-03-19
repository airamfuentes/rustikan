<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Inertia\Inertia;

class CategoriaController extends Controller
{
    /**
     * Muestra todos los establecimientos de una categoría dada.
     */
    public function show(Categoria $categoria)
    {
        $tiendas = $categoria->tiendas()
            ->with(['user', 'categoria'])
            ->withCount('productos')
            ->where('visible', true)
            ->where('activa', true)
            ->orderBy('valoracion', 'desc')
            ->get();

        return Inertia::render('Categorias/Detalle', [
            'categoria' => $categoria,
            'tiendas'   => $tiendas,
        ]);
    }
}
