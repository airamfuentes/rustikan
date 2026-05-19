<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Editar', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status'          => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Email is locked — never overwrite it from this form.
        unset($validated['email']);

        // Recalculate edad from fecha_nacimiento if provided
        if (!empty($validated['fecha_nacimiento'])) {
            $validated['edad'] = \Carbon\Carbon::parse($validated['fecha_nacimiento'])->age;
        }

        // Rebuild direccion from parts if address fields are present
        $calle = $validated['calle'] ?? $request->user()->calle;
        $numero = $validated['numero'] ?? $request->user()->numero;
        $puerta = $validated['puerta'] ?? $request->user()->puerta;
        $cp = $validated['cp'] ?? $request->user()->cp;
        $localidad = $validated['localidad'] ?? $request->user()->localidad;
        if ($calle && $numero && $cp && $localidad) {
            $validated['direccion'] = implode(', ', array_filter([$calle, $numero, $puerta]))
                . ", {$cp} {$localidad}";
        }

        $request->user()->fill($validated);
        $request->user()->save();

        return Redirect::route('profile.edit')
            ->with('success', 'Perfil actualizado correctamente.');
    }

    /**
     * Update the user's avatar photo.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return back()->with('success', 'Foto de perfil actualizada.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
