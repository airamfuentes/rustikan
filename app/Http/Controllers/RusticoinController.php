<?php

namespace App\Http\Controllers;

use App\Mail\RecargaMonedero;
use App\Models\RusticoinTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

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
     * Crea una Stripe Checkout Session para recargar el monedero.
     */
    public function recargar(Request $request)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1|max:500',
        ]);

        $cantidad = (int) $request->cantidad;
        $user     = auth()->user();

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items'           => [
                    [
                        'price_data' => [
                            'currency'     => 'eur',
                            'unit_amount'  => $cantidad * 100,
                            'product_data' => [
                                'name' => "Recarga Monedero RustiCoin ({$cantidad} RC)",
                            ],
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode'        => 'payment',
                'locale'      => 'es',
                'success_url' => route('monedero.recarga.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('monedero.index'),
                'metadata'    => [
                    'user_id'  => $user->id,
                    'cantidad' => $cantidad,
                ],
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('[Stripe recarga monedero] ' . $e->getMessage());
            return back()->withErrors(['stripe' => 'Error al conectar con Stripe. Inténtalo de nuevo.']);
        }

        return Inertia::location($session->url);
    }

    /**
     * Stripe redirige aquí tras el pago de recarga exitoso.
     */
    public function recargaSuccess(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return redirect()->route('monedero.index');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = StripeSession::retrieve($sessionId);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('[Stripe recargaSuccess] retrieve falló: ' . $e->getMessage());
            return redirect()->route('monedero.index')->with('error', 'No se pudo verificar el pago.');
        }

        if ($session->payment_status !== 'paid') {
            return redirect()->route('monedero.index')->with('error', 'El pago no se completó.');
        }

        // Idempotencia: evitar duplicados usando el session ID como referencia
        if (RusticoinTransaction::where('stripe_session_id', $sessionId)->exists()) {
            return redirect()->route('monedero.index')->with('success', 'Tu recarga ya fue procesada anteriormente.');
        }

        $userId   = (int) $session->metadata->user_id;
        $cantidad = (float) $session->metadata->cantidad;

        $user = \App\Models\User::findOrFail($userId);

        // Loguear al usuario si su sesión se perdió en el redirect externo
        if (!auth()->check()) {
            auth()->login($user);
        }

        $nuevoSaldo = null;

        DB::transaction(function () use ($user, $cantidad, $sessionId, &$nuevoSaldo) {
            $nuevoSaldo = (float) $user->rusticoin_balance + $cantidad;
            $user->forceFill(['rusticoin_balance' => $nuevoSaldo])->save();

            RusticoinTransaction::create([
                'user_id'          => $user->id,
                'tipo'             => 'recarga',
                'cantidad'         => $cantidad,
                'saldo_despues'    => $nuevoSaldo,
                'descripcion'      => "Recarga de {$cantidad} RustiCoins vía Stripe",
                'stripe_session_id'=> $sessionId,
            ]);
        });

        Mail::to($user->email)->send(new RecargaMonedero($user, $cantidad, $nuevoSaldo));

        return redirect()->route('monedero.index')
            ->with('success', number_format($cantidad, 2) . ' RustiCoins añadidos a tu monedero.');
    }
}
