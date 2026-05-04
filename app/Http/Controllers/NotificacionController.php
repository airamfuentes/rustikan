<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    /**
     * Devuelve las notificaciones no leídas del usuario autenticado (para el dropdown).
     * Una vez se marcan como leídas se eliminan automáticamente.
     */
    public function index()
    {
        $user = auth()->user();

        $notificaciones = Notificacion::where('user_id', $user->id)
            ->where('leida', false)
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        return response()->json([
            'notificaciones' => $notificaciones,
            'no_leidas'      => $notificaciones->count(),
        ]);
    }

    /**
     * Marcar una notificación como leída → la elimina.
     */
    public function marcarLeida(Notificacion $notificacion)
    {
        if ($notificacion->user_id !== auth()->id()) {
            abort(403);
        }

        $notificacion->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Marcar todas como leídas → elimina todas las no leídas del usuario.
     */
    public function marcarTodasLeidas()
    {
        Notificacion::where('user_id', auth()->id())
            ->where('leida', false)
            ->delete();

        return response()->json(['ok' => true]);
    }
}
