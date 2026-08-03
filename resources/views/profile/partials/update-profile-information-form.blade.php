<section>
    <header>
        <h2 class="text-lg font-medium text-stone-900">
            {{ __('Informations du compte') }}
        </h2>

        <p class="mt-1 text-sm text-stone-600">
            {{ __('Mets à jour le nom affiché et l’adresse e-mail utilisée pour la connexion interne.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Nom')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Adresse e-mail')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button class="bg-[var(--castaneas-brown)] hover:bg-[var(--castaneas-ink)] focus:bg-[var(--castaneas-ink)] active:bg-[var(--castaneas-ink)] focus:ring-[var(--castaneas-brown)]">{{ __('Enregistrer') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-stone-600"
                >{{ __('Enregistré.') }}</p>
            @endif
        </div>
    </form>
</section>
