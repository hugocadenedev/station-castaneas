<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 2xl:flex-row 2xl:items-end 2xl:justify-between">
            <div>
                <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Module 2</div>
                <h1 class="font-display text-3xl leading-tight text-[var(--castaneas-ink)]">Nouveau calibrage</h1>
            </div>
            <a href="{{ route('calibrages.index') }}" class="btn-secondary">Retour à la liste</a>
        </div>
    </x-slot>

    <x-flash-status />

    <div class="space-y-6" x-data="{
        receptionId: @js((string) old('reception_id', '')),
        supplierName: '',
        receptionNumber: '',
        varietyName: '',
        fruitName: '',
        updateReceptionDetails() {
            const selectedOption = this.$refs.receptionSelect.selectedOptions[0];

            if (! selectedOption || ! selectedOption.value) {
                this.supplierName = '';
                this.receptionNumber = '';
                this.varietyName = '';
                this.fruitName = '';
                return;
            }

            this.supplierName = selectedOption.dataset.supplierName || '';
            this.receptionNumber = selectedOption.dataset.receptionNumber || '';
            this.varietyName = selectedOption.dataset.varietyName || '';
            this.fruitName = selectedOption.dataset.fruitName || '';
        },
    }" x-init="$nextTick(() => updateReceptionDetails())">
        <section class="surface rounded-2xl">
            <div class="surface-body">
                <form method="POST" action="{{ route('calibrages.store') }}" class="grid gap-4">
                    @csrf
                    <div>
                        <x-input-label for="reception_id" :value="'Réception à calibrer'" />
                        <select id="reception_id" name="reception_id" x-model="receptionId" x-ref="receptionSelect" x-on:change="updateReceptionDetails()" class="input mt-1 block w-full" required>
                            <option value="">Sélectionner</option>
                            @foreach ($pendingReceptions as $reception)
                                <option
                                    value="{{ $reception->id }}"
                                    data-supplier-name="{{ $reception->supplier->name }}"
                                    data-reception-number="{{ $reception->reception_number }}"
                                    data-variety-name="{{ $reception->variety->name }}"
                                    data-fruit-name="{{ $reception->fruit->name }}"
                                    @selected((string) old('reception_id') === (string) $reception->id)
                                >{{ $reception->reception_number }} - {{ $reception->supplier->name }} - {{ $reception->variety->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('reception_id')" />
                    </div>
                    <div class="grid gap-4 md:grid-cols-2" x-show="receptionId !== ''" x-cloak>
                        <div>
                            <x-input-label for="selected_supplier_name" :value="'Fournisseur'" />
                            <x-text-input id="selected_supplier_name" type="text" class="input mt-1 block w-full" x-bind:value="supplierName" readonly />
                        </div>
                        <div>
                            <x-input-label for="selected_reception_number" :value="'Numéro de réception'" />
                            <x-text-input id="selected_reception_number" type="text" class="input mt-1 block w-full" x-bind:value="receptionNumber" readonly />
                        </div>
                        <div>
                            <x-input-label for="selected_variety_name" :value="'Variété'" />
                            <x-text-input id="selected_variety_name" type="text" class="input mt-1 block w-full" x-bind:value="varietyName" readonly />
                        </div>
                        <div>
                            <x-input-label for="selected_fruit_name" :value="'Fruit'" />
                            <x-text-input id="selected_fruit_name" type="text" class="input mt-1 block w-full" x-bind:value="fruitName" readonly />
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <x-input-label for="caliber_id" :value="'Calibre'" />
                            <select id="caliber_id" name="caliber_id" class="input mt-1 block w-full" required>
                                <option value="">Sélectionner</option>
                                @foreach ($calibers as $caliber)
                                    <option value="{{ $caliber->id }}" @selected((string) old('caliber_id') === (string) $caliber->id)>{{ $caliber->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('caliber_id')" />
                        </div>
                        <div>
                            <x-input-label for="tare_weight_kg" :value="'Tare manuelle (kg)'" />
                            <x-text-input id="tare_weight_kg" name="tare_weight_kg" type="number" step="0.001" min="0" class="input mt-1 block w-full" :value="old('tare_weight_kg', '0.000')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('tare_weight_kg')" />
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <x-input-label for="calibrated_at" :value="'Date'" />
                            <x-text-input id="calibrated_at" name="calibrated_at" type="datetime-local" class="input mt-1 block w-full" :value="old('calibrated_at', now()->format('Y-m-d\TH:i'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('calibrated_at')" />
                        </div>
                        <div>
                            <x-input-label for="net_weight_kg" :value="'Poids net (kg)'" />
                            <x-text-input id="net_weight_kg" name="net_weight_kg" type="number" step="0.001" min="0" class="input mt-1 block w-full" :value="old('net_weight_kg')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('net_weight_kg')" />
                        </div>
                        <div>
                            <x-input-label for="waste_weight_kg" :value="'Poids déchet (kg)'" />
                            <x-text-input id="waste_weight_kg" name="waste_weight_kg" type="number" step="0.001" min="0" class="input mt-1 block w-full" :value="old('waste_weight_kg', '0.000')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('waste_weight_kg')" />
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="under_contract" value="1" class="rounded border-stone-300 text-[var(--castaneas-brown)]" @checked(old('under_contract'))>
                        <span class="text-sm text-stone-700">Sous contrat</span>
                    </label>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button class="btn-primary">Valider le calibrage</button>
                        <a href="{{ route('calibrages.index') }}" class="btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</x-app-layout>