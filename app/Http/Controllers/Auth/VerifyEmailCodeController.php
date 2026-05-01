<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VerifyEmailCodeController extends Controller
{
    /**
     * Verify the 6-digit email verification code.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('home', absolute: false));
        }

        // Check expiry
        if ($user->email_verification_expires_at === null || $user->email_verification_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'code' => 'El código ha expirado. Solicita uno nuevo.',
            ]);
        }

        // Check code
        if ($user->email_verification_code !== $request->code) {
            throw ValidationException::withMessages([
                'code' => 'El código introducido no es correcto.',
            ]);
        }

        // Mark as verified and clear code
        $user->forceFill([
            'email_verified_at'             => now(),
            'email_verification_code'       => null,
            'email_verification_expires_at' => null,
        ])->save();

        return redirect()->intended(route('home', absolute: false))
            ->with('success', '¡Correo verificado! Bienvenido/a a Rustikan.');
    }
}
