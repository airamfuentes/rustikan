<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    /**
     * Devuelve las últimas 20 notificaciones del usuario autenticado (para el dropdown).
     */
    public function index()
    {
        $user = auth()->user();

        $notificaciones = Notificacion::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        $noLeidas = Notificacion::where('user_id', $user->id)
            ->where('leida', false)
            ->count();

        return response()->json([
            'notificaciones' => $notificaciones,
            'no_leidas'      => $noLeidas,
        ]);
    }

    /**
     * Marcar una notificación como leída.
     */
    public function marcarLeida(Notificacion $notificacion)
    {
        if ($notificacion->user_id !== auth()->id()) {
            abort(403);
        }

        $notificacion->update(['leida' => true]);

        return response()->json(['ok' => true]);
    }

    /**
     * Marcar todas las notificaciones del usuario como leídas.
     */
    public function marcarTodasLeidas()
    {
        Notificacion::where('user_id', auth()->id())
            ->where('leida', false)
            ->update(['leida' => true]);

        return response()->json(['ok' => true]);
    }
}
