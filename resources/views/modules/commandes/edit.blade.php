<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Commande</div>
            <h1 class="font-display text-3xl leading-tight text-[var(--castaneas-ink)]">Modifier le numéro</h1>
        </div>
    </x-slot>

    <div class="shell">
        <section class="panel p-6 sm:p-8">
            <form method="POST" action="{{ route('commandes.update', $order) }}" class="max-w-xl space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <x-input-label for="order_number" :value="'Numéro de commande'" />
                    <x-text-input id="order_number" name="order_number" type="text" class="mt-1 block w-full" :value="old('order_number', $order->order_number)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('order_number')" />
                </div>
                <div class="rounded-3xl border border-stone-200 bg-stone-50 px-4 py-4 text-sm leading-6 text-stone-600">
                    Seul le numéro de commande est modifiable. Les palox attribués et les poids prélevés restent figés.
                </div>
                <div class="flex gap-3">
                    <button class="rounded-full bg-[var(--castaneas-brown)] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[var(--castaneas-ink)]">Enregistrer</button>
                    <a href="{{ route('commandes.index') }}" class="rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700">Retour</a>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>