<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Compte</div>
            <h2 class="font-display text-3xl leading-tight text-[var(--castaneas-ink)]">Mon profil</h2>
        </div>
    </x-slot>

    <div class="shell space-y-6">
        <div class="panel p-4 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="panel p-4 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="panel p-4 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
    </div>
</x-app-layout>
