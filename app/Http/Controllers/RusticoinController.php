<?php

namespace App\Http\Controllers;

use App\Models\RusticoinTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RusticoinController extends Controller
{
    /**
     * Página del monedero RustiCoin
     */
    public function index()
    {
        $user = auth()->user();

        $transacciones = $user->rusticoinTransactions()
            ->with('pedido:id,numero_pedido')
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        return Inertia::render('Monedero/Index', [
            'saldo'         => (float) $user->rusticoin_balance,
            'transacciones' => $transacciones,
        ]);
    }

    /**
     * Añadir fondos al monedero (simula pago con tarjeta)
     */
    public function recargar(Request $request)
    {
        $request->validate([
            'cantidad' => 'required|numeric|min:1|max:500',
        ]);

        $user     = auth()->user();
        $cantidad = (float) $request->cantidad;

        DB::transaction(function () use ($user, $cantidad) {
            $nuevoSaldo = (float) $user->rusticoin_balance + $cantidad;

            $user->forceFill(['rusticoin_balance' => $nuevoSaldo])->save();

            RusticoinTransaction::create([
                'user_id'       => $user->id,
                'tipo'          => 'recarga',
                'cantidad'      => $cantidad,
                'saldo_despues' => $nuevoSaldo,
                'descripcion'   => "Recarga de {$cantidad} RustiCoins",
            ]);
        });

        return back()->with('success', number_format($cantidad, 2) . ' RustiCoins añadidos a tu monedero.');
    }

    /**
     * Retirar fondos del monedero (simula reembolso a tarjeta)
     */
    public function retirar(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'cantidad' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value > (float) $user->rusticoin_balance) {
                        $fail('No tienes suficiente saldo para retirar esa cantidad.');
                    }
                },
            ],
        ]);

        $cantidad = (float) $request->cantidad;

        DB::transaction(function () use ($user, $cantidad) {
            $nuevoSaldo = (float) $user->rusticoin_balance - $cantidad;

            $user->forceFill(['rusticoin_balance' => $nuevoSaldo])->save();

            RusticoinTransaction::create([
                'user_id'       => $user->id,
                'tipo'          => 'retiro',
                'cantidad'      => -$cantidad,
                'saldo_despues' => $nuevoSaldo,
                'descripcion'   => "Retirada de {$cantidad} RustiCoins",
            ]);
        });

        return back()->with('success', number_format($cantidad, 2) . ' RustiCoins retirados. El reembolso se procesará en 5-10 días hábiles.');
    }
}
