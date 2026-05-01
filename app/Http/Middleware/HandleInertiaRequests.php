<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $ownerTienda = null;
        if ($user && $user->role === 'owner') {
            $ownerTienda = $user->tiendas()->first(['id', 'nombre', 'slug']);
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user'   => $user,
                'tienda' => $ownerTienda,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
                'info'    => $request->session()->get('info'),
                'warning' => $request->session()->get('warning'),
            ],
            'recaptchaSiteKey' => config('services.recaptcha.site_key'),
        ];
    }
}
