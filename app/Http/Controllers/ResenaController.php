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

        // Admin: sin restricciones de pedidos. Una reseña libre por admin
        // (mantenemos compatibilidad con el flujo previo: actualiza si ya tiene una).
        if ($user->role === 'admin') {
            $resena = Resena::firstOrNew([
                'user_id'   => $user->id,
                'tienda_id' => $tienda->id,
                'pedido_id' => null,
            ]);
            $resena->fill($validated);
            $resena->user_id   = $user->id;
            $resena->tienda_id = $tienda->id;
            $resena->save();

            $tienda->recalcularValoracion();

            return back()->with('success', '¡Gracias por tu opinión! Tu reseña se ha publicado.');
        }

        // Usuario normal: 1 reseña por pedido. Buscamos el primer pedido suyo
        // en esta tienda que aún no tenga reseña asociada.
        $pedidoSinResena = Pedido::query()
            ->where('user_id', $user->id)
            ->whereNotIn('estado', ['cancelado'])
            ->whereDoesntHave('resena')
            ->whereHas('items', fn ($q) => $q->where('tienda_id', $tienda->id))
            ->orderBy('created_at')
            ->first();

        if (!$pedidoSinResena) {
            // O no tiene pedidos en la tienda, o ya reseñó cada uno de ellos.
            $tienePedido = PedidoItem::where('tienda_id', $tienda->id)
                ->whereHas('pedido', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->whereNotIn('estado', ['cancelado']);
                })
                ->exists();

            $msg = $tienePedido
                ? 'Ya has reseñado todos tus pedidos en esta tienda.'
                : 'Solo puedes reseñar una tienda donde hayas realizado un pedido.';

            return back()->with('error', $msg);
        }

        Resena::create(array_merge($validated, [
            'user_id'   => $user->id,
            'tienda_id' => $tienda->id,
            'pedido_id' => $pedidoSinResena->id,
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
