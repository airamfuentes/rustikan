<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Inertia\Inertia;

/**
 * El owner NO puede editar la tienda directamente. Esta clase solo sirve la
 * vista del formulario; el envío real va a SolicitudController para que sea
 * el admin quien apruebe cada cambio antes de aplicarlo.
 */
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
}
