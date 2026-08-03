<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6 space-y-2">
        <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Connexion</div>
        <h1 class="font-display text-3xl text-[var(--castaneas-ink)]">Accès interne Castaneas</h1>
        <p class="text-sm leading-6 text-stone-600">Connecte-toi avec un compte créé par le backoffice. L’inscription publique est désactivée.</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Adresse e-mail')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Mot de passe')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-stone-300 text-[var(--castaneas-brown)] shadow-sm focus:ring-[var(--castaneas-brown)]" name="remember">
                <span class="ms-2 text-sm text-stone-600">{{ __('Se souvenir de moi') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="rounded-md text-sm text-stone-600 underline underline-offset-4 transition hover:text-stone-900 focus:outline-none focus:ring-2 focus:ring-[var(--castaneas-brown)] focus:ring-offset-2" href="{{ route('password.request') }}">
                    {{ __('Mot de passe oublié ?') }}
                </a>
            @endif

            <x-primary-button class="ms-3 bg-[var(--castaneas-brown)] hover:bg-[var(--castaneas-ink)] focus:bg-[var(--castaneas-ink)] active:bg-[var(--castaneas-ink)] focus:ring-[var(--castaneas-brown)]">
                {{ __('Se connecter') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
