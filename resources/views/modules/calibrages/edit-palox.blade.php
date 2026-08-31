<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Administration</div>
                <h1 class="font-display text-3xl leading-tight text-[var(--castaneas-ink)]">Modifier {{ $palox->palox_number }}</h1>
                <div class="mt-2 text-sm text-stone-500">Réception {{ $palox->reception->reception_number }}</div>
            </div>
            <a href="{{ $backRoute }}" class="btn-secondary">Retour au calibrage</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl" x-data="{ caliberId: @js((string) old('caliber_id', $palox->calibration->caliber_id)), wasteWeight: @js((string) old('waste_weight_kg', $palox->calibration->waste_weight_kg)), hasSignificantWaste() { return Number(this.wasteWeight || 0) > 1; } }" x-init="$nextTick(() => { if (hasSignificantWaste()) caliberId = ''; })">
        <section class="surface rounded-2xl">
            <div class="surface-body">
                <form method="POST" action="{{ route('calibrages.paloxes.update', $palox) }}" class="grid gap-4">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <x-input-label for="caliber_id" :value="'Calibre'" />
                            <select id="caliber_id" name="caliber_id" x-model="caliberId" x-bind:disabled="hasSignificantWaste()" class="input mt-1 block w-full">
                                <option value="">Sans calibre (déchet)</option>
                                @foreach ($calibers as $caliber)
                                    <option value="{{ $caliber->id }}" @selected((string) old('caliber_id', $palox->calibration->caliber_id) === (string) $caliber->id)>{{ $caliber->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('caliber_id')" />
                        </div>
                        <div>
                            <x-input-label for="tare_type_id" :value="'Type de tare'" />
                            <select id="tare_type_id" name="tare_type_id" class="input mt-1 block w-full" required>
                                <option value="{{ $manualTareTypeId }}">Saisie manuelle</option>
                                @foreach ($tareTypes as $tareType)
                                    <option value="{{ $tareType->id }}" @selected((string) old('tare_type_id', $palox->calibration->tare_type_id) === (string) $tareType->id)>{{ $tareType->label }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('tare_type_id')" />
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <x-input-label for="calibrated_at" :value="'Date'" />
                            <x-text-input id="calibrated_at" name="calibrated_at" type="datetime-local" class="input mt-1 block w-full" :value="old('calibrated_at', $palox->calibration->calibrated_at->format('Y-m-d\TH:i'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('calibrated_at')" />
                        </div>
                        <div>
                            <x-input-label for="net_weight_kg" :value="'Poids net (kg)'" />
                            <x-text-input id="net_weight_kg" name="net_weight_kg" type="number" step="0.001" min="0" class="input mt-1 block w-full" :value="old('net_weight_kg', $palox->calibration->net_weight_kg)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('net_weight_kg')" />
                        </div>
                        <div>
                            <x-input-label for="waste_weight_kg" :value="'Poids déchet (kg)'" />
                            <x-text-input id="waste_weight_kg" name="waste_weight_kg" type="number" step="0.001" min="0" class="input mt-1 block w-full" x-model="wasteWeight" x-on:input="if (hasSignificantWaste()) caliberId = ''" required />
                            <x-input-error class="mt-2" :messages="$errors->get('waste_weight_kg')" />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="tare_weight_kg" :value="'Poids de tare (kg)'" />
                        <x-text-input id="tare_weight_kg" name="tare_weight_kg" type="number" step="0.001" min="0" class="input mt-1 block w-full" :value="old('tare_weight_kg', $palox->calibration->tare_weight_kg)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('tare_weight_kg')" />
                    </div>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="under_contract" value="1" class="rounded border-stone-300 text-[var(--castaneas-brown)]" @checked(old('under_contract', $palox->under_contract))>
                        <span class="text-sm text-stone-700">Sous contrat</span>
                    </label>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button class="btn-primary">Enregistrer les modifications</button>
                        <a href="{{ $backRoute }}" class="btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</x-app-layout>