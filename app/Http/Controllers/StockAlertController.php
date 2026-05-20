<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\StockAlert;
use Illuminate\Http\Request;

class StockAlertController extends Controller
{
    /**
     * Toggle stock alert subscription for the authenticated user.
     */
    public function toggle(Request $request, Producto $producto)
    {
        $user = $request->user();

        $existing = StockAlert::where('user_id', $user->id)
            ->where('producto_id', $producto->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['subscribed' => false]);
        }

        StockAlert::create([
            'user_id'     => $user->id,
            'producto_id' => $producto->id,
            'email'       => $user->email,
        ]);

        return response()->json(['subscribed' => true]);
    }

    /**
     * Check which of the user's subscribed products the user is subscribed to.
     * Returns a list of producto_ids the user is watching.
     */
    public function misAlertas(Request $request)
    {
        $ids = StockAlert::where('user_id', $request->user()->id)
            ->pluck('producto_id');

        return response()->json(['producto_ids' => $ids]);
    }

    /**
     * Check real-time stock for a list of product IDs (used by cart before checkout).
     */
    public function checkCarrito(Request $request)
    {
        $request->validate([
            'items'           => 'required|array',
            'items.*.id'      => 'required|integer',
            'items.*.cantidad' => 'required|integer|min:1',
        ]);

        $user     = $request->user();
        $alertIds = $user
            ? StockAlert::where('user_id', $user->id)->pluck('producto_id')->toArray()
            : [];

        $results = [];
        foreach ($request->items as $item) {
            $producto = Producto::find($item['id']);

            if (!$producto) {
                $results[] = [
                    'id'           => $item['id'],
                    'ok'           => false,
                    'stock'        => 0,
                    'stock_minimo' => 0,
                    'disponible'   => false,
                    'subscribed'   => false,
                ];
                continue;
            }

            $ok = $producto->disponible && $producto->stock >= $item['cantidad'];

            $results[] = [
                'id'           => $producto->id,
                'nombre'       => $producto->nombre,
                'ok'           => $ok,
                'stock'        => (int) $producto->stock,
                'stock_minimo' => (int) $producto->stock_minimo,
                'disponible'   => (bool) $producto->disponible,
                'subscribed'   => in_array($producto->id, $alertIds),
            ];
        }

        return response()->json(['items' => $results]);
    }
}
