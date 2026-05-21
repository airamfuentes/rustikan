<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
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

        $validated = $request->validate([
            'puntuacion' => 'required|integer|min:1|max:5',
            'titulo'     => 'nullable|string|max:120',
            'comentario' => 'required|string|min:10|max:1000',
        ]);

        // Debe tener al menos un pedido entregado en esta tienda en los últimos 30 días
        $tienePedido = Pedido::where('user_id', $user->id)
            ->where('estado', 'entregado')
            ->where('created_at', '>=', now()->subDays(30))
            ->whereHas('items', fn ($q) => $q->where('tienda_id', $tienda->id))
            ->exists();

        if (!$tienePedido) {
            return back()->with('error', 'Solo puedes reseñar tiendas en las que hayas realizado un pedido entregado en los últimos 30 días.');
        }

        // 1 reseña por usuario por tienda
        $yaReseno = Resena::where('user_id', $user->id)
            ->where('tienda_id', $tienda->id)
            ->exists();

        if ($yaReseno) {
            return back()->with('error', 'Ya has dejado una reseña en esta tienda.');
        }

        // Asociar el pedido más reciente entregado en esta tienda
        $pedido = Pedido::where('user_id', $user->id)
            ->where('estado', 'entregado')
            ->where('created_at', '>=', now()->subDays(30))
            ->whereHas('items', fn ($q) => $q->where('tienda_id', $tienda->id))
            ->latest()
            ->first();

        Resena::create(array_merge($validated, [
            'user_id'   => $user->id,
            'tienda_id' => $tienda->id,
            'pedido_id' => $pedido?->id,
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
