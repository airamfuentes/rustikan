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
        // Dominios de correo permitidos (los más conocidos)
        $dominiosPermitidos = [
            'gmail.com', 'googlemail.com',
            'outlook.com', 'outlook.es', 'hotmail.com', 'hotmail.es', 'live.com', 'msn.com',
            'yahoo.com', 'yahoo.es',
            'icloud.com', 'me.com', 'mac.com',
            'protonmail.com', 'proton.me',
            'aol.com', 'gmx.com', 'gmx.es',
            'rustikan.com',
        ];

        $request->validate([
            'name'             => 'required|string|min:2|max:60|regex:/^[\pL\s\'-]+$/u',
            'apellidos'        => 'required|string|min:2|max:80|regex:/^[\pL\s\'-]+$/u',
            'telefono'         => ['required', 'string', function ($attr, $value, $fail) {
                $digits = preg_replace('/^\+\d{1,3}/', '', $value); // strip country prefix
                $digits = preg_replace('/\D/', '', $digits);
                if (!preg_match('/^[6-9]\d{8}$/', $digits)) {
                    $fail('El teléfono debe tener 9 dígitos y empezar por 6, 7, 8 o 9.');
                }
            }],
            'email'            => [
                'required', 'string', 'lowercase', 'email:rfc,dns', 'max:255',
                'unique:'.User::class,
                function ($attr, $value, $fail) use ($dominiosPermitidos) {
                    $dominio = strtolower(substr(strrchr($value, '@') ?: '', 1));
                    if (!in_array($dominio, $dominiosPermitidos)) {
                        $fail('Solo se permiten correos de proveedores conocidos (Gmail, Outlook, Hotmail, Yahoo, iCloud, Proton, etc.).');
                    }
                },
            ],
            'fecha_nacimiento' => [
                'required', 'date',
                'before_or_equal:' . now()->subYears(14)->format('Y-m-d'),
                'after_or_equal:' . now()->subYears(120)->format('Y-m-d'),
            ],
            'direccion'        => 'required|string|min:5|max:500',
            'accept_terms'     => 'accepted',
            'password'         => ['required', 'confirmed', Rules\Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'turnstile_token'  => 'required|string',
        ], [
            'name.regex'                  => 'El nombre solo puede contener letras.',
            'apellidos.regex'             => 'Los apellidos solo pueden contener letras.',
            'fecha_nacimiento.before_or_equal' => 'Debes tener al menos 14 años.',
            'fecha_nacimiento.after_or_equal'  => 'Fecha de nacimiento no válida.',
        ]);

        // Verify Turnstile with Cloudflare
        $turnstile = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret'   => config('services.turnstile.secret'),
            'response' => $request->input('turnstile_token'),
            'remoteip' => $request->ip(),
        ]);

        if (! $turnstile->json('success')) {
            throw ValidationException::withMessages([
                'turnstile_token' => 'La verificación ha fallado. Inténtalo de nuevo.',
            ]);
        }

        $edad = \Carbon\Carbon::parse($request->fecha_nacimiento)->age;

        $user = User::create([
            'name'             => $request->name,
            'apellidos'        => $request->apellidos,
            'telefono'         => $request->telefono,
            'email'            => $request->email,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'edad'             => $edad,
            'direccion'        => $request->direccion,
            'password'         => Hash::make($request->password),
        ]);

        event(new Registered($user));

        // Registrar actividad
        ActivityLog::log(
            'nuevo_usuario',
            "Nuevo usuario registrado: {$user->name}",
            'usuario',
            'blue',
            $user
        );

        Auth::login($user);

        return redirect(route('verification.notice', absolute: false))
            ->with('success', "¡Bienvenido a Rustikan, {$user->name}! Verifica tu email para empezar.");
    }
}
