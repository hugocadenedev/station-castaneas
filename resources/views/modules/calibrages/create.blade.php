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
        receptionId: @js((string) old('reception_id', $selectedReceptionId)),
        fruitId: '',
        supplierName: '',
        receptionNumber: '',
        varietyName: '',
        fruitName: '',
        caliberId: @js((string) old('caliber_id', '')),
        netWeight: @js((string) old('net_weight_kg', '')),
        wasteWeight: @js((string) old('waste_weight_kg', '0.000')),
        availableCalibers: [],
        calibersByFruit: @js($calibersByFruit),
        savedPaloxesByReception: @js($savedPaloxesByReception),
        savedPaloxes: [],
        tareTypeId: @js((string) old('tare_type_id', (string) $manualTareTypeId)),
        tareWeight: @js((string) old('tare_weight_kg', '0.000')),
        updateReceptionDetails() {
            const selectedOption = this.$refs.receptionSelect.selectedOptions[0];

            if (! selectedOption || ! selectedOption.value) {
                this.fruitId = '';
                this.supplierName = '';
                this.receptionNumber = '';
                this.varietyName = '';
                this.fruitName = '';
                this.availableCalibers = [];
                this.savedPaloxes = [];
                this.caliberId = '';
                return;
            }

            this.fruitId = selectedOption.dataset.fruitId || '';
            this.supplierName = selectedOption.dataset.supplierName || '';
            this.receptionNumber = selectedOption.dataset.receptionNumber || '';
            this.varietyName = selectedOption.dataset.varietyName || '';
            this.fruitName = selectedOption.dataset.fruitName || '';
            this.availableCalibers = this.calibersByFruit[this.fruitId] || [];
            this.savedPaloxes = this.savedPaloxesByReception[this.receptionId] || [];

            if (! this.availableCalibers.some((caliber) => caliber.id === this.caliberId)) {
                this.caliberId = '';
            }
        },
        updateTareWeight() {
            const selectedOption = this.$refs.tareTypeSelect.selectedOptions[0];

            if (! selectedOption || ! selectedOption.value) {
                this.tareWeight = '0.000';
                return;
            }

            this.tareWeight = selectedOption.dataset.weightKg || '0.000';
        },
        hasSignificantWaste() {
            return Number(this.wasteWeight || 0) > 1;
        },
    }" x-init="$nextTick(() => { updateReceptionDetails(); updateTareWeight(); if (hasSignificantWaste()) caliberId = ''; })">
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
                                    data-fruit-id="{{ $reception->fruit_id }}"
                                    data-supplier-name="{{ $reception->supplier->supplier_code }}"
                                    data-reception-number="{{ $reception->reception_number }}"
                                    data-variety-name="{{ $reception->variety->name }}"
                                    data-fruit-name="{{ $reception->fruit->name }}"
                                    @selected((string) old('reception_id') === (string) $reception->id)
                                >{{ $reception->reception_number }} - {{ $reception->supplier->supplier_code }} - {{ $reception->variety->name }}</option>
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
                            <select id="caliber_id" name="caliber_id" x-model="caliberId" x-bind:disabled="hasSignificantWaste()" class="input mt-1 block w-full">
                                <option value="">Sélectionner</option>
                                <template x-for="caliber in availableCalibers" :key="caliber.id">
                                    <option :value="caliber.id" x-text="caliber.name"></option>
                                </template>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('caliber_id')" />
                        </div>
                        <div>
                            <x-input-label for="tare_type_id" :value="'Type de tare'" />
                            <select id="tare_type_id" name="tare_type_id" x-model="tareTypeId" x-ref="tareTypeSelect" x-on:change="updateTareWeight()" class="input mt-1 block w-full" required>
                                <option value="{{ $manualTareTypeId }}" data-weight-kg="0.000">Saisie manuelle</option>
                                @foreach ($tareTypes as $tareType)
                                    <option value="{{ $tareType->id }}" data-weight-kg="{{ number_format((float) $tareType->weight_kg, 3, '.', '') }}">{{ $tareType->label }} ({{ number_format((float) $tareType->weight_kg, 3, ',', ' ') }} kg)</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('tare_type_id')" />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="tare_weight_kg" :value="'Poids de tare (kg)'" />
                        <input id="tare_weight_kg" name="tare_weight_kg" type="number" step="0.001" min="0" class="input mt-1 block w-full" x-model="tareWeight" required>
                            <x-input-error class="mt-2" :messages="$errors->get('tare_weight_kg')" />
                    </div>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <x-input-label for="calibrated_at" :value="'Date'" />
                            <x-text-input id="calibrated_at" name="calibrated_at" type="datetime-local" class="input mt-1 block w-full" :value="old('calibrated_at', now()->format('Y-m-d\TH:i'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('calibrated_at')" />
                        </div>
                        <div>
                            <x-input-label for="net_weight_kg" :value="'Poids net (kg)'" />
                            <x-text-input id="net_weight_kg" name="net_weight_kg" type="number" step="0.001" min="0" class="input mt-1 block w-full" x-model="netWeight" />
                            <x-input-error class="mt-2" :messages="$errors->get('net_weight_kg')" />
                        </div>
                        <div>
                            <x-input-label for="waste_weight_kg" :value="'Poids déchet (kg)'" />
                            <x-text-input id="waste_weight_kg" name="waste_weight_kg" type="number" step="0.001" min="0" class="input mt-1 block w-full" x-model="wasteWeight" x-on:input="if (hasSignificantWaste()) caliberId = ''" required />
                            <x-input-error class="mt-2" :messages="$errors->get('waste_weight_kg')" />
                        </div>
                    </div>
                    <div class="rounded-2xl border border-dashed border-stone-300 bg-stone-50 px-4 py-3 text-sm text-stone-600">
                        Chaque validation ajoute un palox au calibrage en cours. Les étiquettes deviennent disponibles juste après l'enregistrement, puis tu peux valider le calibrage une fois tous les palox saisis.
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button class="btn-primary">Ajouter ce palox</button>
                        <a href="{{ route('calibrages.index') }}" class="btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </section>

        <section class="surface rounded-2xl" x-show="receptionId !== ''" x-cloak>
            <div class="surface-body space-y-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Palox déjà saisis</h2>
                        <p class="text-sm text-stone-500">Imprime l'étiquette dès qu'un palox est complet, puis continue la saisie si besoin.</p>
                        <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-[var(--castaneas-brown)]/10 px-3 py-1 text-sm font-semibold text-[var(--castaneas-brown)]">
                            <span>Compteur</span>
                            <span x-text="savedPaloxes.length === 0 ? 'Aucun palox' : `Palox ${savedPaloxes.length} prêt${savedPaloxes.length > 1 ? 's' : ''}`"></span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <form method="POST" x-bind:action="receptionId ? '{{ url('/receptions') }}/' + receptionId + '/calibrage/retirer-dernier' : '#'">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger" x-bind:disabled="savedPaloxes.length === 0" x-bind:class="savedPaloxes.length === 0 ? 'opacity-50 cursor-not-allowed' : ''" onclick="return confirm('Retirer le dernier palox saisi ?');">Supprimer le dernier palox</button>
                        </form>
                        <form method="POST" x-bind:action="receptionId ? '{{ url('/receptions') }}/' + receptionId + '/calibrage/finaliser' : '#'">
                            @csrf
                            <button type="submit" class="btn-primary" x-bind:disabled="savedPaloxes.length === 0" x-bind:class="savedPaloxes.length === 0 ? 'opacity-50 cursor-not-allowed' : ''">Valider le calibrage</button>
                        </form>
                    </div>
                </div>

                <template x-if="savedPaloxes.length === 0">
                    <div class="rounded-2xl border border-stone-200 bg-white px-4 py-6 text-sm text-stone-500">
                        Aucun palox enregistré pour cette réception pour l'instant.
                    </div>
                </template>

                <template x-if="savedPaloxes.length > 0">
                    <div class="overflow-x-auto">
                        <table class="data-table tablet-stack">
                            <thead>
                                <tr>
                                    <th>Étape</th>
                                    <th>Palox</th>
                                    <th>Calibre</th>
                                    <th>Poids net</th>
                                    <th>Étiquette</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 bg-white">
                                <template x-for="(palox, index) in savedPaloxes" :key="palox.id">
                                    <tr>
                                        <td data-label="Étape" class="font-semibold text-stone-800" x-text="`Palox ${index + 1}`"></td>
                                        <td data-label="Palox" class="font-semibold text-stone-800" x-text="palox.palox_number"></td>
                                        <td data-label="Calibre" x-text="palox.caliber_name"></td>
                                        <td data-label="Poids net"><div x-text="`${palox.net_weight_kg} kg`"></div><div class="text-xs text-stone-500" x-text="`Déchet: ${palox.waste_weight_kg} kg`"></div></td>
                                        <td data-label="Étiquette"><a :href="palox.label_url" class="text-sm font-semibold text-[var(--castaneas-bordeaux)]">Imprimer</a></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>
        </section>
    </div>
</x-app-layout>