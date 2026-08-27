<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Module 3</div>
            <h1 class="font-display text-3xl leading-tight text-[var(--castaneas-ink)]">Stock</h1>
        </div>
    </x-slot>

    <x-flash-status />

    <div class="space-y-6" x-data="{ tab: '{{ request('tab', 'general') }}' }">
        <div class="flex gap-2 border-b border-stone-200 pb-3">
            <button @click="tab = 'general'" :class="tab === 'general' ? 'bg-stone-900 text-white border-stone-900' : 'bg-white text-stone-700 border-stone-300'" class="rounded-lg border px-4 py-2 text-sm font-semibold">Stock général</button>
            <button @click="tab = 'non-conforming'" :class="tab === 'non-conforming' ? 'bg-stone-900 text-white border-stone-900' : 'bg-white text-stone-700 border-stone-300'" class="rounded-lg border px-4 py-2 text-sm font-semibold">Non conformes</button>
        </div>

        <section class="space-y-6" x-show="tab === 'general'">
            <article class="surface overflow-hidden rounded-2xl">
                <div class="toolbar">
                    <form method="GET" class="grid w-full gap-6">
                        <input type="hidden" name="tab" value="general">
                        <div class="grid gap-4 lg:grid-cols-2">
                            <section class="rounded-2xl border border-stone-200 bg-stone-50/60 p-4">
                                <div class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Origine</div>
                                <div class="grid gap-4">
                                    <div>
                                        <x-input-label for="fruit_id" :value="'Fruit'" />
                                        <select id="fruit_id" name="fruit_id" onchange="this.form.submit()" class="input mt-1 w-full">
                                            <option value="">Tous les fruits</option>
                                            @foreach ($fruits as $fruit)
                                                <option value="{{ $fruit->id }}" @selected((string) request('fruit_id') === (string) $fruit->id)>{{ $fruit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="supplier_id" :value="'Fournisseur'" />
                                        <select id="supplier_id" name="supplier_id" class="input mt-1 w-full">
                                            <option value="">Tous les fournisseurs</option>
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" @selected((string) request('supplier_id') === (string) $supplier->id)>{{ $supplier->supplier_code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="variety_id" :value="'Variété'" />
                                        <select id="variety_id" name="variety_id" class="input mt-1 w-full">
                                            <option value="">Toutes les variétés</option>
                                            @foreach ($varieties as $variety)
                                                <option value="{{ $variety->id }}" @selected((string) request('variety_id') === (string) $variety->id)>{{ $variety->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </section>

                            <section class="rounded-2xl border border-stone-200 bg-stone-50/60 p-4">
                                <div class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Palox</div>
                                <div class="grid gap-4">
                                    <div>
                                        <x-input-label for="palox_number" :value="'Numéro palox'" />
                                        <input id="palox_number" type="text" name="palox_number" value="{{ request('palox_number') }}" placeholder="Rechercher un numéro" class="input mt-1 w-full">
                                    </div>
                                    <div>
                                        <x-input-label for="caliber_id" :value="'Calibre'" />
                                        <select id="caliber_id" name="caliber_id" class="input mt-1 w-full">
                                            <option value="">Tous les calibres</option>
                                            @foreach ($calibers as $caliber)
                                                <option value="{{ $caliber->id }}" @selected((string) request('caliber_id') === (string) $caliber->id)>{{ $caliber->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <x-input-label for="net_weight_min" :value="'Poids restant min (kg)'" />
                                            <input id="net_weight_min" type="number" step="0.001" min="0" name="net_weight_min" value="{{ request('net_weight_min') }}" class="input mt-1 w-full">
                                        </div>
                                        <div>
                                            <x-input-label for="net_weight_max" :value="'Poids restant max (kg)'" />
                                            <input id="net_weight_max" type="number" step="0.001" min="0" name="net_weight_max" value="{{ request('net_weight_max') }}" class="input mt-1 w-full">
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                        <section class="rounded-2xl border border-stone-200 bg-white p-4">
                            <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <x-input-label for="under_contract" :value="'Certifié'" />
                                        <select id="under_contract" name="under_contract" class="input mt-1 w-full">
                                            <option value="">Tous</option>
                                            <option value="1" @selected(request('under_contract') === '1')>Oui</option>
                                            <option value="0" @selected(request('under_contract') === '0')>Non</option>
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="availability_status" :value="'État'" />
                                        <select id="availability_status" name="availability_status" class="input mt-1 w-full">
                                            <option value="">Tous les états</option>
                                            <option value="available" @selected(request('availability_status') === 'available')>Disponible</option>
                                            <option value="partial" @selected(request('availability_status') === 'partial')>Partiel</option>
                                            <option value="reserved" @selected(request('availability_status') === 'reserved')>Réservé</option>
                                            <option value="exhausted" @selected(request('availability_status') === 'exhausted')>Épuisé</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-3 sm:flex-row lg:justify-end">
                                    <button class="btn-primary">Filtrer</button>
                                    <a href="{{ route('stock.index') }}" class="btn-secondary">Réinitialiser</a>
                                </div>
                            </div>
                        </section>
                    </form>
                </div>
            </article>
            <article class="surface overflow-hidden rounded-2xl">
                <div class="overflow-x-auto">
                    <table class="data-table tablet-stack">
                        <thead>
                            <tr>
                                <th>Fruit</th>
                                <th>Fournisseur</th>
                                <th>Variété</th>
                                <th>Numéro palox</th>
                                <th>Calibre</th>
                                <th>Poids net</th>
                                <th>Certifié</th>
                                <th>État</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 bg-white">
                            @forelse ($paloxes as $palox)
                                <tr>
                                    <td data-label="Fruit">{{ $palox->reception->fruit->name }}</td>
                                    <td data-label="Fournisseur">{{ $palox->reception->supplier->supplier_code }}</td>
                                    <td data-label="Variété">{{ $palox->reception->variety->name }}</td>
                                    <td data-label="Numéro palox" class="font-semibold text-stone-800">{{ $palox->palox_number }}</td>
                                    <td data-label="Calibre">{{ $palox->calibration?->caliber?->name ?? 'Sans calibre (déchet)' }}</td>
                                    <td data-label="Poids net">{{ number_format((float) $palox->remaining_net_weight_kg, 3, ',', ' ') }} kg</td>
                                    <td data-label="Certifié">
                                        @if ($palox->under_contract)
                                            <span class="pill pill-ok">Oui</span>
                                        @else
                                            <span class="pill pill-warn">Non</span>
                                        @endif
                                    </td>
                                    <td data-label="État">
                                        @if ($palox->availability_status === 'available')
                                            <span class="pill pill-ok">Disponible</span>
                                        @elseif ($palox->availability_status === 'partial')
                                            <span class="pill pill-warn">Partiel</span>
                                        @elseif ($palox->availability_status === 'reserved')
                                            <span class="pill pill-warn">Réservé</span>
                                        @else
                                            <span class="pill pill-alert">Épuisé</span>
                                        @endif
                                    </td>
                                    <td data-label="Actions">
                                        <div class="flex flex-col gap-2">
                                            <a href="{{ route('stock.show', $palox) }}" class="text-sm font-semibold text-[var(--castaneas-bordeaux)]">Traçabilité</a>
                                            <a href="{{ route('paloxes.label', $palox) }}" class="text-sm font-semibold text-[var(--castaneas-bordeaux)]">Réimprimer</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="px-4 py-6 text-center text-stone-500">Aucun palox en stock.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="surface-header">{{ $paloxes->links() }}</div>
            </article>
        </section>

        <section class="space-y-6" x-show="tab === 'non-conforming'">
            <article class="surface overflow-hidden rounded-2xl">
                <div class="toolbar">
                    <form method="GET" class="grid gap-3 md:grid-cols-[1fr_1fr_auto] w-full">
                        <input type="hidden" name="tab" value="non-conforming">
                        <input type="text" name="non_conforming_reception_number" value="{{ request('non_conforming_reception_number') }}" placeholder="N° réception" class="input">
                        <input type="number" name="non_conforming_supplier_id" value="{{ request('non_conforming_supplier_id') }}" placeholder="ID fournisseur" class="input">
                        <button class="btn-primary">Filtrer</button>
                    </form>
                </div>
            </article>
            <article class="surface overflow-hidden rounded-2xl">
                <div class="overflow-x-auto">
                    <table class="data-table tablet-stack">
                        <thead>
                            <tr>
                                <th>Réception</th>
                                <th>Origine</th>
                                <th>Poids brut</th>
                                <th>Motif</th>
                                <th>Étiquette</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 bg-white">
                            @forelse ($nonConformingReceptions as $reception)
                                <tr>
                                    <td data-label="Réception" class="font-semibold text-stone-800">{{ $reception->reception_number }}</td>
                                    <td data-label="Origine">{{ $reception->supplier->supplier_code }}<div class="text-xs text-stone-500">{{ $reception->fruit->name }} - {{ $reception->variety->name }}</div></td>
                                    <td data-label="Poids brut">{{ $reception->gross_weight_kg !== null ? number_format((float) $reception->gross_weight_kg, 3, ',', ' ').' kg' : 'À renseigner' }}</td>
                                    <td data-label="Motif">{{ $reception->non_conformity_reason }}</td>
                                    <td data-label="Actions"><a href="{{ route('receptions.label', $reception) }}" class="text-sm font-semibold text-[var(--castaneas-bordeaux)]">Étiquette</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-stone-500">Aucune réception non conforme.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="surface-header">{{ $nonConformingReceptions->links() }}</div>
            </article>
        </section>
    </div>
</x-app-layout>