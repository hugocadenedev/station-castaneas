<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#2a211d">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Castaneas">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
        <link rel="icon" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('icon.svg') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-stone-900 antialiased">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-10">
            <div class="mb-6 text-center">
                <a href="/" class="inline-flex flex-col items-center gap-2 text-center">
                    <span class="pill border-stone-300 bg-white/80 text-stone-600">Station fruitiere</span>
                    <span class="font-display text-4xl font-semibold tracking-tight text-[var(--castaneas-brown)]">Castaneas</span>
                </a>
            </div>

            <div class="grid w-full max-w-5xl gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <section class="panel hidden min-h-[32rem] overflow-hidden lg:flex lg:flex-col lg:justify-between">
                    <div class="space-y-6 p-10">
                        <span class="pill border-[rgba(125,47,47,0.18)] bg-[rgba(125,47,47,0.06)] text-[var(--castaneas-bordeaux)]">Pilotage atelier</span>
                        <div class="space-y-4">
                            <h1 class="font-display text-5xl leading-tight text-[var(--castaneas-ink)]">Traçabilité complète, de la réception au départ client.</h1>
                            <p class="max-w-xl text-base leading-7 text-stone-600">Cette base projet prépare les modules Réception, Calibrage, Stock, Commandes et Backoffice avec une authentification interne, des rôles et un schéma de données pensé pour le suivi GGN et palox.</p>
                        </div>
                    </div>
                    <div class="grid gap-4 border-t border-stone-200/80 bg-[rgba(246,240,230,0.6)] p-8 sm:grid-cols-3">
                        <div class="metric-card">
                            <div class="text-sm text-stone-500">Auth</div>
                            <div class="mt-2 font-display text-2xl text-[var(--castaneas-brown)]">Interne</div>
                        </div>
                        <div class="metric-card">
                            <div class="text-sm text-stone-500">Déploiement</div>
                            <div class="mt-2 font-display text-2xl text-[var(--castaneas-brown)]">Mutualisé</div>
                        </div>
                        <div class="metric-card">
                            <div class="text-sm text-stone-500">Impression</div>
                            <div class="mt-2 font-display text-2xl text-[var(--castaneas-brown)]">Étiquette PDF</div>
                        </div>
                    </div>
                </section>

                <section class="panel w-full overflow-hidden px-6 py-6 sm:px-8 sm:py-8">
                    {{ $slot }}
                </section>
            </div>
        </div>
    </body>
</html>
