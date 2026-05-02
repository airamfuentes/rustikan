<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSupplier
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $role = auth()->user()->role;
        if ($role !== 'supplier' && $role !== 'admin') {
            abort(403, 'No tienes permisos para acceder a esta área.');
        }

        return $next($request);
    }
}
