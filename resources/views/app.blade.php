<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Rustikan') }}</title>

        <!-- Favicon (logo_item.png) -->
        <link rel="icon" type="image/png" sizes="any" href="{{ asset('images/logo_item.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo_item.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo_item.png') }}">
        <meta name="theme-color" content="#ea580c">

        <!-- Canonical -->
        <link rel="canonical" href="{{ url()->current() }}">

        <!-- Meta description por defecto (cada página puede sobrescribirla con <Head> de Inertia) -->
        <meta name="description" content="Rustikan – Mercado local online de Lanzarote. Compra productos frescos km0 directamente a productores artesanales con entrega a domicilio en la isla.">
        <meta name="keywords" content="Lanzarote, productos locales, km0, mercado online, productores Lanzarote, frutas, vinos La Geria, queso majorero, artesanía Canarias, entrega a domicilio">

        <!-- Open Graph / Facebook -->
        <meta property="og:site_name" content="Rustikan">
        <meta property="og:type" content="website">
        <meta property="og:title" content="Rustikan – Mercado local de Lanzarote">
        <meta property="og:description" content="Productos frescos km0 de productores de Lanzarote, con entrega a domicilio. Vinos de La Geria, quesos artesanales, miel, aloe vera y mucho más.">
        <meta property="og:image" content="{{ asset('images/logo_item.png') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:locale" content="es_ES">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="Rustikan – Mercado local de Lanzarote">
        <meta name="twitter:description" content="Productos frescos km0 de productores de Lanzarote, con entrega a domicilio.">
        <meta name="twitter:image" content="{{ asset('images/logo_item.png') }}">

        <!-- Google structured data: Organization + Logo -->
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "Organization",
            "name": "Rustikan",
            "url": "{{ url('/') }}",
            "logo": "{{ asset('images/logo_item.png') }}",
            "description": "Mercado local online de Lanzarote: productos frescos km0 con entrega a domicilio.",
            "areaServed": {
                "@@type": "Place",
                "name": "Lanzarote, Islas Canarias, España"
            },
            "contactPoint": {
                "@@type": "ContactPoint",
                "email": "info@rustikan.com",
                "contactType": "customer support",
                "availableLanguage": ["Spanish", "English"]
            }
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
