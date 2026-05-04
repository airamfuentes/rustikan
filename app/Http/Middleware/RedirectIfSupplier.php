<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfSupplier
{
    private const ALLOWED_PREFIXES = [
        '/supplier',
        '/logout',
        '/login',
        '/api/',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'supplier') {
            return $next($request);
        }

        $path = '/' . ltrim($request->path(), '/');

        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if ($path === rtrim($prefix, '/') || str_starts_with($path, rtrim($prefix, '/') . '/')) {
                return $next($request);
            }
        }

        return redirect()->route('supplier.dashboard');
    }
}
