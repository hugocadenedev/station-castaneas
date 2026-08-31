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
            search: '',
            filteredCatalog() {
                const term = this.search.trim().toLowerCase();

                if (! term) {
                    return this.catalog;
                }

                return this.catalog.filter((entry) => entry.number.toLowerCase().includes(term));
            },
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
                            <x-input-label for="order_number" :value="'Numéro de commande'" />
                            <x-text-input id="order_number" name="order_number" type="text" class="input mt-1 block w-full" :value="old('order_number', request('order_number'))" />
                            <x-input-error class="mt-2" :messages="$errors->get('order_number')" />
                        </div>
                        <div>
                            <x-input-label for="ordered_at" :value="'Date de commande'" />
                            <x-text-input id="ordered_at" name="ordered_at" type="datetime-local" class="input mt-1 block w-full" :value="old('ordered_at', request('ordered_at', now()->format('Y-m-d\TH:i')))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('ordered_at')" />
                        </div>
                    </div>

                    <div class="grid gap-4 2xl:grid-cols-4">
                        <div>
                            <x-input-label for="fruit_id" :value="'Filtre fruit pour les palox'" />
                            <select id="fruit_id" name="fruit_id" onchange="applyPaloxFilter('fruit_id', this.value)" class="input mt-1 block w-full">
                                <option value="">Tous les fruits</option>
                                @foreach ($fruits as $fruit)
                                    <option value="{{ $fruit->id }}" @selected((string) request('fruit_id') === (string) $fruit->id)>{{ $fruit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="variety_id" :value="'Filtre variété pour les palox'" />
                            <select id="variety_id" name="variety_id" onchange="applyPaloxFilter('variety_id', this.value)" class="input mt-1 block w-full">
                                <option value="">Toutes les variétés</option>
                                @foreach ($varieties as $variety)
                                    <option value="{{ $variety->id }}" @selected((string) request('variety_id') === (string) $variety->id)>{{ $variety->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="caliber_id" :value="'Filtre calibre pour les palox'" />
                            <select id="caliber_id" name="caliber_id" onchange="applyPaloxFilter('caliber_id', this.value)" class="input mt-1 block w-full">
                                <option value="">Tous les calibres</option>
                                @foreach ($calibers as $caliber)
                                    <option value="{{ $caliber->id }}" @selected((string) request('caliber_id') === (string) $caliber->id)>{{ $caliber->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="supplier_id" :value="'Filtre fournisseur pour les palox'" />
                            <select id="supplier_id" name="supplier_id" onchange="applyPaloxFilter('supplier_id', this.value)" class="input mt-1 block w-full">
                                <option value="">Tous les fournisseurs</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected((string) request('supplier_id') === (string) $supplier->id)>{{ $supplier->supplier_code }}</option>
                                @endforeach
                            </select>
                        </div>
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
                            <div class="border-b border-stone-200 px-4 py-3">
                                <input type="search" x-model="search" placeholder="Rechercher un numéro de palox…" class="input block w-full sm:max-w-xs">
                            </div>
                            <div class="hidden overflow-x-auto xl:block">
                                <table class="data-table">
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
                                        <template x-for="entry in filteredCatalog()" :key="entry.id">
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
                                        <tr x-show="filteredCatalog().length === 0">
                                            <td colspan="6" class="px-4 py-6 text-center text-stone-500">Aucun palox disponible pour ce filtre.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="divide-y divide-stone-100 xl:hidden">
                                <template x-for="entry in filteredCatalog()" :key="entry.id">
                                    <div class="flex items-center gap-3 px-4 py-3">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                                <span class="font-semibold text-stone-800" x-text="entry.number"></span>
                                                <span :class="entry.statusClass" x-text="entry.status"></span>
                                            </div>
                                            <div class="mt-0.5 truncate text-xs text-stone-500" x-text="`${entry.supplier} · ${entry.fruit} - ${entry.variety} · ${entry.caliber}`"></div>
                                            <div class="mt-0.5 text-xs font-medium text-stone-600" x-text="`${entry.remainingWeight} / ${entry.initialWeight} kg`"></div>
                                        </div>
                                        <button
                                            type="button"
                                            class="btn-secondary h-9 shrink-0 px-3 text-xs"
                                            :disabled="isSelected(entry.id)"
                                            :class="isSelected(entry.id) ? 'cursor-not-allowed opacity-50' : ''"
                                            @click="addPalox(entry)"
                                            x-text="isSelected(entry.id) ? 'Ajouté' : 'Ajouter'"
                                        ></button>
                                    </div>
                                </template>
                                <div x-show="filteredCatalog().length === 0" class="px-4 py-6 text-center text-sm text-stone-500">Aucun palox disponible pour ce filtre.</div>
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

    <script>
        function applyPaloxFilter(field, value) {
            const params = new URLSearchParams(window.location.search);
            params.set(field, value);

            const orderNumber = document.getElementById('order_number')?.value;
            const orderedAt = document.getElementById('ordered_at')?.value;

            if (orderNumber) {
                params.set('order_number', orderNumber);
            }

            if (orderedAt) {
                params.set('ordered_at', orderedAt);
            }

            window.location.search = params.toString();
        }
    </script>
</x-app-layout>