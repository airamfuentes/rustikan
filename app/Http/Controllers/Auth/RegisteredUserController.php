<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'                 => 'required|string|max:255',
            'apellidos'            => 'required|string|max:255',
            'telefono'             => 'required|string|min:9|max:20|unique:users,telefono',
            'email'                => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'edad'                 => 'nullable|integer|min:14|max:120',
            'direccion'            => 'nullable|string|max:500',
            'sms_verification_code'=> 'required|string|min:4|max:10',
            'accept_terms'         => 'accepted',
            'password'             => ['required', 'confirmed', Rules\Password::defaults()],
            'recaptcha_token'      => 'required|string',
        ]);

        // Verify reCAPTCHA with Google
        $recaptcha = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => config('services.recaptcha.secret'),
            'response' => $request->input('recaptcha_token'),
            'remoteip' => $request->ip(),
        ]);

        if (! $recaptcha->json('success')) {
            throw ValidationException::withMessages([
                'recaptcha_token' => 'La verificación reCAPTCHA ha fallado. Inténtalo de nuevo.',
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'apellidos' => $request->apellidos,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'edad' => $request->edad,
            'direccion' => $request->direccion,
            'telefono_verificado_at' => now(),
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        // Registrar actividad
        ActivityLog::log(
            'nuevo_usuario',
            "Nuevo usuario registrado: {$user->name}",
            '👤',
            'blue',
            $user
        );

        Auth::login($user);

        return redirect(route('verification.notice', absolute: false));
    }
}
