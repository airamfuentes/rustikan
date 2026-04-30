<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user  = $request->user();
        $nombre = $user->name;

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('success', "¡Hola de nuevo, {$nombre}! Bienvenido al panel de administración.");
        }

        if ($user->isOwner()) {
            return redirect()->route('owner.panel')
                ->with('success', "¡Hola de nuevo, {$nombre}! Bienvenido a tu panel de tienda.");
        }

        return redirect()->route('home')
            ->with('success', "¡Hola de nuevo, {$nombre}! Nos alegra verte por aquí.");
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
