<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 2xl:flex-row 2xl:items-end 2xl:justify-between">
            <div>
                <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Module 2</div>
                <h1 class="font-display text-3xl leading-tight text-[var(--castaneas-ink)]">Détail du calibrage</h1>
                <div class="mt-2 text-sm text-stone-500">Réception {{ $reception->reception_number }}</div>
            </div>
            <a href="{{ route('calibrages.index') }}" class="btn-secondary">Retour à la liste</a>
        </div>
    </x-slot>

    <x-flash-status />

    @php
        $calibrations = $reception->calibrations->sortBy('calibrated_at')->values();
        $paloxes = $reception->paloxes->sortBy('labeled_at')->values();
    @endphp

    <div class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-2">
            <article class="surface rounded-2xl">
                <div class="surface-header">
                    <h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Origine</h2>
                </div>
                <div class="surface-body grid gap-3 text-sm leading-6 text-stone-700 sm:grid-cols-2">
                    <div><strong>Réception :</strong><div>{{ $reception->reception_number }}</div></div>
                    <div><strong>Date réception :</strong><div>{{ $reception->received_at->format('d/m/Y H:i') }}</div></div>
                    <div><strong>Fournisseur :</strong><div>{{ $reception->supplier->supplier_code }}</div></div>
                    <div><strong>Fruit / Variété :</strong><div>{{ $reception->fruit->name }} / {{ $reception->variety->name }}</div></div>
                    <div><strong>Opérateur réception :</strong><div>{{ $reception->operator->name }}</div></div>
                    <div><strong>Poids brut :</strong><div>{{ $reception->gross_weight_kg !== null ? number_format((float) $reception->gross_weight_kg, 3, ',', ' ').' kg' : 'À renseigner' }}</div></div>
                </div>
            </article>

            <article class="surface rounded-2xl">
                <div class="surface-header">
                    <h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Synthèse</h2>
                </div>
                <div class="surface-body grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Palox</div>
                        <div class="mt-2 text-2xl font-semibold text-stone-900">{{ $paloxes->count() }}</div>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Poids net total</div>
                        <div class="mt-2 text-2xl font-semibold text-stone-900">{{ number_format((float) $calibrations->sum('net_weight_kg'), 3, ',', ' ') }} kg</div>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Déchet total</div>
                        <div class="mt-2 text-2xl font-semibold text-stone-900">{{ number_format((float) $calibrations->sum('waste_weight_kg'), 3, ',', ' ') }} kg</div>
                    </div>
                </div>
            </article>
        </section>

        <section class="surface overflow-hidden rounded-2xl">
            <div class="surface-header">
                <h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Palox du calibrage</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table tablet-stack">
                    <thead>
                        <tr>
                            <th>Palox</th>
                            <th>Calibre</th>
                            <th>Poids</th>
                            <th>Tare</th>
                            <th>Opérateur</th>
                            <th>Étiquette</th>
                            @if (auth()->user()->hasRole('superadmin'))
                                <th>Administration</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse ($paloxes as $palox)
                            <tr>
                                <td data-label="Palox"><div class="font-semibold text-stone-800">{{ $palox->palox_number }}</div><div class="text-xs text-stone-500">{{ $palox->labeled_at->format('d/m/Y H:i') }}</div></td>
                                <td data-label="Calibre">{{ $palox->calibration->caliber?->name ?? 'Sans calibre (déchet)' }}</td>
                                <td data-label="Poids">{{ number_format((float) $palox->initial_net_weight_kg, 3, ',', ' ') }} kg<div class="text-xs text-stone-500">Déchet: {{ number_format((float) $palox->calibration->waste_weight_kg, 3, ',', ' ') }} kg</div></td>
                                <td data-label="Tare">{{ $palox->calibration->tareType->label }}<div class="text-xs text-stone-500">{{ number_format((float) $palox->calibration->tare_weight_kg, 3, ',', ' ') }} kg</div></td>
                                <td data-label="Opérateur">{{ $palox->calibration->operator->name }}</td>
                                <td data-label="Étiquette"><a href="{{ route('paloxes.label', $palox) }}" class="text-sm font-semibold text-[var(--castaneas-bordeaux)]">Étiquette</a></td>
                                @if (auth()->user()->hasRole('superadmin'))
                                    <td data-label="Administration" class="space-y-2"><a href="{{ route('calibrages.paloxes.edit', $palox) }}" class="block text-sm font-semibold text-[var(--castaneas-bordeaux)]">Modifier</a><form method="POST" action="{{ route('calibrages.paloxes.destroy', $palox) }}" onsubmit="return confirm('Supprimer ce palox et son calibrage ?');">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-700">Supprimer</button></form></td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ auth()->user()->hasRole('superadmin') ? 7 : 6 }}" class="px-4 py-6 text-center text-stone-500">Aucun palox trouvé pour ce calibrage.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>