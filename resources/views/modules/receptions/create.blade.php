<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Module 1</div>
            <h1 class="font-display text-3xl leading-tight text-[var(--castaneas-ink)]">Nouvelle réception</h1>
        </div>
    </x-slot>

    <div>
        <section class="surface rounded-2xl" x-data="{ fruitId: '{{ old('fruit_id') }}', conformityStatus: '{{ old('conformity_status', 'conforming') }}' }">
            <div class="surface-body">
            <form method="POST" action="{{ route('receptions.store') }}" class="grid gap-5 2xl:grid-cols-2">
                @csrf
                <div>
                    <x-input-label for="received_at" :value="'Date de réception'" />
                    <x-text-input id="received_at" name="received_at" type="datetime-local" class="input mt-1 block w-full" :value="old('received_at', now()->format('Y-m-d\TH:i'))" required />
                    <x-input-error class="mt-2" :messages="$errors->get('received_at')" />
                </div>
                <div>
                    <x-input-label for="supplier_id" :value="'Fournisseur'" />
                    <select id="supplier_id" name="supplier_id" class="input mt-1 block w-full" required>
                        <option value="">Sélectionner</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>{{ $supplier->supplier_code }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('supplier_id')" />
                </div>
                <div>
                    <x-input-label for="fruit_id" :value="'Fruit'" />
                    <select id="fruit_id" name="fruit_id" x-model="fruitId" class="input mt-1 block w-full" required>
                        <option value="">Sélectionner</option>
                        @foreach ($fruits as $fruit)
                            <option value="{{ $fruit->id }}" @selected((string) old('fruit_id') === (string) $fruit->id)>{{ $fruit->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('fruit_id')" />
                </div>
                <div>
                    <x-input-label for="variety_id" :value="'Variété'" />
                    <select id="variety_id" name="variety_id" class="input mt-1 block w-full" required>
                        <option value="">Sélectionner</option>
                        @foreach ($fruits as $fruit)
                            @foreach ($fruit->varieties as $variety)
                                <option x-show="String(fruitId || '{{ old('fruit_id') }}') === '{{ $fruit->id }}'" value="{{ $variety->id }}" @selected((string) old('variety_id') === (string) $variety->id)>{{ $variety->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('variety_id')" />
                </div>
                <div>
                    <x-input-label for="gross_weight_kg" :value="'Poids brut (kg)'" />
                    <x-text-input id="gross_weight_kg" name="gross_weight_kg" type="number" step="0.001" min="0" class="input mt-1 block w-full" :value="old('gross_weight_kg')" />
                    <p class="mt-2 text-xs text-stone-500">Ce champ peut être laissé vide et complété plus tard.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('gross_weight_kg')" />
                </div>
                <div>
                    <x-input-label for="conformity_status" :value="'Conformité'" />
                    <select id="conformity_status" name="conformity_status" x-model="conformityStatus" class="input mt-1 block w-full" required>
                        <option value="conforming" @selected(old('conformity_status', 'conforming') === 'conforming')>Conforme</option>
                        <option value="non_conforming" @selected(old('conformity_status') === 'non_conforming')>Non conforme</option>
                    </select>
                </div>
                <div class="2xl:col-span-2">
                    <div x-show="conformityStatus === 'non_conforming'" x-cloak>
                        <x-input-label for="non_conformity_reason" :value="'Explication de non-conformité'" />
                        <textarea id="non_conformity_reason" name="non_conformity_reason" rows="4" class="textarea mt-1 block w-full" x-bind:required="conformityStatus === 'non_conforming'" placeholder="Justifiez la non-conformité constatée">{{ old('non_conformity_reason') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('non_conformity_reason')" />
                    </div>
                </div>
                <div class="2xl:col-span-2 flex flex-col gap-3 sm:flex-row">
                    <button class="btn-primary">Enregistrer la réception</button>
                    <a href="{{ route('receptions.index') }}" class="btn-secondary">Retour</a>
                </div>
            </form>
            </div>
        </section>
    </div>
</x-app-layout>