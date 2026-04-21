<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->role !== 'owner') {
            abort(403, 'No tienes permisos para acceder al panel de propietario.');
        }

        if (!$user->tiendas()->exists()) {
            abort(403, 'Tu cuenta no tiene ninguna tienda asignada. Contacta con el administrador.');
        }

        return $next($request);
    }
}
