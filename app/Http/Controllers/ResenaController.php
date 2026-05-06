<?php

namespace App\Http\Controllers;

use App\Models\PedidoItem;
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

        // Verificar que el usuario tiene al menos un pedido (solo para role=user)
        if ($user->role === 'user') {
            $tienePedido = PedidoItem::where('tienda_id', $tienda->id)
                ->whereHas('pedido', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->whereNotIn('estado', ['cancelado']);
                })
                ->exists();

            if (!$tienePedido) {
                return back()->with('error', 'Solo puedes reseñar una tienda donde hayas realizado un pedido.');
            }
        }

        $validated = $request->validate([
            'puntuacion' => 'required|integer|min:1|max:5',
            'titulo'     => 'nullable|string|max:120',
            'comentario' => 'required|string|min:10|max:1000',
        ]);

        // Crear o actualizar (upsert por user+tienda)
        Resena::updateOrCreate(
            ['user_id' => $user->id, 'tienda_id' => $tienda->id],
            array_merge($validated, ['user_id' => $user->id, 'tienda_id' => $tienda->id])
        );

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
