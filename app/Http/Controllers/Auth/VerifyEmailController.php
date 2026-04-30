<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home')
                ->with('success', '¡Tu correo ya estaba verificado! Bienvenido/a de nuevo a Rustikan.');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        $nombre = $request->user()->name;

        return redirect()->route('home')
            ->with('success', "¡Cuenta verificada, {$nombre}! Bienvenido/a a Rustikan. Ya puedes explorar y comprar productos locales.");
    }
}
