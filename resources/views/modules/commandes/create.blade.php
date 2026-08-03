<x-app-layout>
    @php
        $paloxCatalog = $availablePaloxes->map(function ($palox) {
            $statusLabel = $palox->availability_status === 'partial' ? 'Partiel' : 'Disponible';
            $remainingWeight = round((float) $palox->remaining_net_weight_kg, 3);

            return [
                'id' => (string) $palox->id,
                'number' => $palox->palox_number,
                'supplier' => $palox->reception->supplier->supplier_code,
                'fruit' => $palox->reception->fruit->name,
                'variety' => $palox->reception->variety->name,
                'caliber' => $palox->calibration->caliber->name,
                'remainingWeight' => number_format($remainingWeight, 3, ',', ' '),
                'remainingWeightValue' => number_format($remainingWeight, 3, '.', ''),
                'initialWeight' => number_format((float) $palox->initial_net_weight_kg, 3, ',', ' '),
                'status' => $statusLabel,
                'statusClass' => $palox->availability_status === 'partial' ? 'pill pill-warn' : 'pill pill-ok',
                'details' => $palox->reception->supplier->supplier_code.' - '.$palox->reception->fruit->name.' / '.$palox->reception->variety->name.' - calibre '.$palox->calibration->caliber->name.' - '.$statusLabel,
            ];
        })->values();

        $selectedPaloxes = collect(old('lines', []))
            ->map(function ($line) use ($paloxCatalog) {
                $paloxId = (string) ($line['palox_id'] ?? '');
                $catalogEntry = $paloxCatalog->firstWhere('id', $paloxId);

                if (! $catalogEntry) {
                    return null;
                }

                return [
                    'palox_id' => $paloxId,
                    'number' => $catalogEntry['number'],
                    'details' => $catalogEntry['details'],
                    'remainingWeight' => $catalogEntry['remainingWeight'],
                    'remainingWeightValue' => $catalogEntry['remainingWeightValue'],
                    'pickedWeight' => $line['picked_net_weight_kg'] ?? $catalogEntry['remainingWeightValue'],
                ];
            })
            ->filter()
            ->values();
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 2xl:flex-row 2xl:items-end 2xl:justify-between">
            <div>
                <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Module 4</div>
                <h1 class="font-display text-3xl leading-tight text-[var(--castaneas-ink)]">Nouvelle commande</h1>
            </div>
            <a href="{{ route('commandes.index') }}" class="btn-secondary">Retour à la liste</a>
        </div>
    </x-slot>

    <x-flash-status />

    <div
        class="space-y-6"
        x-data="{
            catalog: @js($paloxCatalog),
            rows: @js($selectedPaloxes),
            isSelected(paloxId) {
                return this.rows.some((row) => row.palox_id === paloxId);
            },
            addPalox(entry) {
                if (this.isSelected(entry.id)) {
                    return;
                }

                this.rows.push({
                    palox_id: entry.id,
                    number: entry.number,
                    details: entry.details,
                    remainingWeight: entry.remainingWeight,
                    remainingWeightValue: entry.remainingWeightValue,
                    pickedWeight: entry.remainingWeightValue,
                });
            },
            removeRow(index) {
                this.rows.splice(index, 1);
            },
        }"
    >
        <section class="surface rounded-2xl">
            <div class="surface-body">
                <form method="POST" action="{{ route('commandes.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid gap-4 2xl:grid-cols-2">
                        <div>
                            <x-input-label for="customer_id" :value="'Client référencé'" />
                            <select id="customer_id" name="customer_id" class="input mt-1 block w-full">
                                <option value="">Client ponctuel / saisie libre</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected((string) old('customer_id') === (string) $customer->id)>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('customer_id')" />
                        </div>
                        <div>
                            <x-input-label for="client_name" :value="'Nom client si hors liste'" />
                            <x-text-input id="client_name" name="client_name" type="text" class="input mt-1 block w-full" :value="old('client_name')" />
                            <x-input-error class="mt-2" :messages="$errors->get('client_name')" />
                        </div>
                    </div>

                    <div class="grid gap-4 2xl:grid-cols-2">
                        <div>
                            <x-input-label for="order_number" :value="'Numéro de commande'" />
                            <x-text-input id="order_number" name="order_number" type="text" class="input mt-1 block w-full" :value="old('order_number')" />
                            <x-input-error class="mt-2" :messages="$errors->get('order_number')" />
                        </div>
                        <div>
                            <x-input-label for="ordered_at" :value="'Date de commande'" />
                            <x-text-input id="ordered_at" name="ordered_at" type="datetime-local" class="input mt-1 block w-full" :value="old('ordered_at', now()->format('Y-m-d\TH:i'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('ordered_at')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="variety_id" :value="'Filtre variété pour les palox'" />
                        <select id="variety_id" name="variety_id" onchange="window.location='?variety_id='+this.value" class="input mt-1 block w-full">
                            <option value="">Toutes les variétés</option>
                            @foreach ($varieties as $variety)
                                <option value="{{ $variety->id }}" @selected((string) request('variety_id') === (string) $variety->id)>{{ $variety->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-3">
                        <div class="surface overflow-hidden rounded-2xl border border-stone-200">
                            <div class="surface-header flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Palox disponibles</h2>
                                    <p class="text-sm text-stone-500">Choisir directement depuis la liste stock pour alimenter la commande.</p>
                                </div>
                                <div class="text-sm text-stone-500" x-text="rows.length ? `${rows.length} palox sélectionné(s)` : 'Aucun palox sélectionné'"></div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="data-table tablet-stack">
                                    <thead>
                                        <tr>
                                            <th>Palox</th>
                                            <th>Origine</th>
                                            <th>Calibre</th>
                                            <th>Poids restant</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-stone-100 bg-white">
                                        <template x-for="entry in catalog" :key="entry.id">
                                            <tr>
                                                <td data-label="Palox" class="font-semibold text-stone-800" x-text="entry.number"></td>
                                                <td data-label="Origine">
                                                    <div x-text="entry.supplier"></div>
                                                    <div class="text-xs text-stone-500" x-text="`${entry.fruit} - ${entry.variety}`"></div>
                                                </td>
                                                <td data-label="Calibre" x-text="entry.caliber"></td>
                                                <td data-label="Poids restant" x-text="`${entry.remainingWeight} / ${entry.initialWeight} kg`"></td>
                                                <td data-label="Statut">
                                                    <span :class="entry.statusClass" x-text="entry.status"></span>
                                                </td>
                                                <td data-label="Actions">
                                                    <button
                                                        type="button"
                                                        class="btn-secondary h-9 px-3 text-xs"
                                                        :disabled="isSelected(entry.id)"
                                                        :class="isSelected(entry.id) ? 'cursor-not-allowed opacity-50' : ''"
                                                        @click="addPalox(entry)"
                                                        x-text="isSelected(entry.id) ? 'Déjà ajouté' : 'Ajouter'"
                                                    ></button>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="catalog.length === 0">
                                            <td colspan="6" class="px-4 py-6 text-center text-stone-500">Aucun palox disponible pour ce filtre.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Palox retenus</h2>
                                <div class="text-sm text-stone-500">Saisir la quantité exacte à prélever pour chaque palox.</div>
                            </div>
                            <template x-if="rows.length === 0">
                                <div class="rounded-2xl border border-dashed border-stone-300 bg-stone-50 px-4 py-5 text-sm text-stone-500">
                                    Sélectionner un ou plusieurs palox dans le tableau ci-dessus.
                                </div>
                            </template>
                            <template x-for="(row, index) in rows" :key="row.palox_id">
                                <div class="grid gap-3 rounded-2xl border border-stone-200 bg-stone-50 p-3 lg:grid-cols-[minmax(0,1fr)_260px_48px]">
                                    <div class="rounded-xl border border-stone-200 bg-white px-4 py-3">
                                        <input :name="`lines[${index}][palox_id]`" x-model="row.palox_id" type="hidden">
                                        <div class="font-semibold text-stone-800" x-text="row.number"></div>
                                        <div class="mt-1 text-xs leading-5 text-stone-500" x-text="row.details"></div>
                                    </div>
                                    <div class="rounded-xl border border-stone-200 bg-white px-4 py-3">
                                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Poids à prélever</div>
                                        <input :name="`lines[${index}][picked_net_weight_kg]`" x-model="row.pickedWeight" type="number" step="0.001" min="0.001" :max="row.remainingWeightValue" class="input mt-2 block w-full">
                                        <div class="mt-2 text-xs text-stone-500">Disponible : <span x-text="`${row.remainingWeight} kg`"></span></div>
                                    </div>
                                    <button type="button" @click="removeRow(index)" class="rounded-lg border border-stone-300 text-sm font-semibold text-stone-700">-</button>
                                </div>
                            </template>
                        </div>
                        @error('lines')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                        @error('lines.*.picked_net_weight_kg')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button class="btn-primary">Enregistrer la commande</button>
                        <a href="{{ route('commandes.index') }}" class="btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</x-app-layout>