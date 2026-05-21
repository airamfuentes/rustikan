<?php

namespace App\Http\Controllers;

use App\Models\Resena;
use App\Models\Tienda;
use Illuminate\Http\Request;

class ResenaController extends Controller
{
    public function store(Request $request, Tienda $tienda)
    {
        $user = auth()->user();

        // Solo clientes y admins pueden reseñar
        if (!in_array($user->role, ['user', 'admin'])) {
            return back()->with('error', 'Solo los clientes pueden dejar reseñas.');
        }

        $validated = $request->validate([
            'puntuacion' => 'required|integer|min:1|max:5',
            'titulo'     => 'nullable|string|max:120',
            'comentario' => 'required|string|min:10|max:1000',
        ]);

        // 1 reseña por usuario por tienda (sin restricción de pedido previo por ahora)
        $yaReseno = Resena::where('user_id', $user->id)
            ->where('tienda_id', $tienda->id)
            ->exists();

        if ($yaReseno) {
            return back()->with('error', 'Ya has dejado una reseña en esta tienda.');
        }

        Resena::create(array_merge($validated, [
            'user_id'   => $user->id,
            'tienda_id' => $tienda->id,
            'pedido_id' => null,
        ]));

        $tienda->recalcularValoracion();

        return back()->with('success', '¡Gracias por tu opinión! Tu reseña se ha publicado.');
    }

    public function destroy(Resena $resena)
    {
        // Solo el autor puede eliminar su reseña
        if ($resena->user_id !== auth()->id()) {
            abort(403);
        }

        $tienda = $resena->tienda;
        $resena->delete();
        $tienda->recalcularValoracion();

        return back()->with('success', 'Tu reseña ha sido eliminada.');
    }
}
