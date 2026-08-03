<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Castaneas') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-stone-900 antialiased">
        <main class="shell flex min-h-screen items-center justify-center py-10">
            <section class="panel w-full max-w-md px-8 py-8 sm:px-10">
                <div class="mb-6 space-y-2 text-center">
                    <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Castaneas Station</div>
                    <h1 class="font-display text-3xl text-[var(--castaneas-ink)]">Connexion</h1>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="email" :value="'Adresse e-mail'" />
                        <x-text-input id="email" class="input mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" :value="'Mot de passe'" />
                        <x-text-input id="password" class="input mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-stone-600">
                        <input id="remember_me" type="checkbox" class="rounded border-stone-300 text-[var(--castaneas-brown)] shadow-sm focus:ring-[var(--castaneas-brown)]" name="remember">
                        <span>Se souvenir de moi</span>
                    </label>

                    <button class="btn-primary w-full justify-center">Se connecter</button>
                </form>
            </section>
        </main>
    </body>
</html>
