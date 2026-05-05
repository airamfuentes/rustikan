<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;

class FacturaController extends Controller
{
    /**
     * Muestra una factura imprimible para un pedido.
     * Solo el propietario del pedido, el owner de la tienda, o un admin pueden verla.
     */
    public function show(Pedido $pedido)
    {
        $user = auth()->user();

        // Autorización: cliente dueño del pedido, admin, o owner de la tienda
        $ownerTiendaId = $user->role === 'owner' ? $user->tiendas()->value('id') : null;
        $esOwner = $ownerTiendaId && $pedido->items()->where('tienda_id', $ownerTiendaId)->exists();
        $esAdmin  = $user->role === 'admin';
        $esCliente = $pedido->user_id === $user->id;

        if (!$esCliente && !$esAdmin && !$esOwner) {
            abort(403);
        }

        $pedido->load([
            'user:id,name,email',
            'items.producto:id,nombre,imagen',
        ]);

        return view('facturas.show', compact('pedido'));
    }
}
