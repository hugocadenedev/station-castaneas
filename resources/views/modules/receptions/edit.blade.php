<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 2xl:flex-row 2xl:items-end 2xl:justify-between">
            <div>
                <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Module 1</div>
                <h1 class="font-display text-3xl leading-tight text-[var(--castaneas-ink)]">Modifier la réception</h1>
            </div>
            <a href="{{ route('receptions.index') }}" class="btn-secondary">Retour à la liste</a>
        </div>
    </x-slot>

    <x-flash-status />

    <section class="surface rounded-2xl">
        <div class="surface-body">
            <div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Réception</div>
                    <div class="mt-1 text-sm text-stone-800">{{ $reception->reception_number }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Fournisseur</div>
                    <div class="mt-1 text-sm text-stone-800">{{ $reception->supplier->supplier_code }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Fruit / Variété</div>
                    <div class="mt-1 text-sm text-stone-800">{{ $reception->fruit->name }} / {{ $reception->variety->name }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Date</div>
                    <div class="mt-1 text-sm text-stone-800">{{ $reception->received_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('receptions.update', $reception) }}" class="grid gap-5 md:max-w-md">
                @csrf
                @method('PATCH')
                <div>
                    <x-input-label for="gross_weight_kg" :value="'Poids brut (kg)'
                    " />
                    <x-text-input id="gross_weight_kg" name="gross_weight_kg" type="number" step="0.001" min="0" class="input mt-1 block w-full" :value="old('gross_weight_kg', $reception->gross_weight_kg !== null ? number_format((float) $reception->gross_weight_kg, 3, '.', '') : '')" />
                    <p class="mt-2 text-xs text-stone-500">Tu peux renseigner ce champ après la création de la réception.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('gross_weight_kg')" />
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <button class="btn-primary">Enregistrer</button>
                    <a href="{{ route('receptions.index') }}" class="btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </section>
</x-app-layout>