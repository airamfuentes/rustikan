<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Rustikan') }}</title>

        <!-- SEO / Open Graph -->
        <meta name="description" content="Rustikan – Mercado local online. Compra productos frescos y artesanales directamente de productores de tu zona.">
        <meta property="og:site_name" content="Rustikan">
        <meta property="og:image" content="{{ url('/images/logo_item.png') }}">
        <meta property="og:type" content="website">
        <meta name="twitter:card" content="summary">
        <meta name="twitter:image" content="{{ url('/images/logo_item.png') }}">

        <!-- Google structured data: Organization + Logo -->
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "Organization",
            "name": "Rustikan",
            "url": "{{ url('/') }}",
            "logo": "{{ url('/images/logo_item.png') }}"
        }
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Dark mode: apply class before render to prevent FOUC -->
        <script>
            (function(){
                var t=localStorage.getItem('theme');
                if(t==='dark'||(t===null&&window.matchMedia('(prefers-color-scheme: dark)').matches)){
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead

        <!-- Cloudflare Turnstile -->
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
