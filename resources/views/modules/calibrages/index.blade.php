<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 2xl:flex-row 2xl:items-end 2xl:justify-between">
            <div>
                <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Module 2</div>
                <h1 class="font-display text-3xl leading-tight text-[var(--castaneas-ink)]">Calibrage</h1>
            </div>
            <a href="{{ route('calibrages.create') }}" class="btn-primary">Nouveau calibrage</a>
        </div>
    </x-slot>

    <x-flash-status />

    <div class="space-y-6">
        <section class="surface overflow-hidden rounded-2xl">
            <div class="toolbar">
                <form method="GET" class="grid w-full gap-3 md:grid-cols-[1fr_1fr_auto]">
                        <input type="text" name="reception_number" value="{{ request('reception_number') }}" placeholder="N° réception" class="input">
                        <select name="caliber_id" class="input">
                            <option value="">Tous les calibres</option>
                            @foreach ($calibers as $caliber)
                                <option value="{{ $caliber->id }}" @selected((string) request('caliber_id') === (string) $caliber->id)>{{ $caliber->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn-primary">Filtrer</button>
                    </form>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table tablet-stack">
                    <thead>
                        <tr>
                            <th>Réception</th>
                            <th>Origine</th>
                            <th>Palox</th>
                            <th>Poids total</th>
                            <th>Dernier calibrage</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse ($receptions as $reception)
                            <tr>
                                <td data-label="Réception">
                                    <div class="font-semibold text-stone-800">{{ $reception->reception_number }}</div>
                                    <div class="text-xs text-stone-500">{{ optional($reception->calibrations_max_calibrated_at)->format('d/m/Y H:i') }}</div>
                                </td>
                                <td data-label="Origine">{{ $reception->supplier->supplier_code }}<div class="text-xs text-stone-500">{{ $reception->fruit->name }} - {{ $reception->variety->name }}</div></td>
                                <td data-label="Palox">{{ $reception->paloxes_count }} palox</td>
                                <td data-label="Poids">{{ number_format((float) ($reception->calibrations_sum_net_weight_kg ?? 0), 3, ',', ' ') }} kg<div class="text-xs text-stone-500">Déchet: {{ number_format((float) ($reception->calibrations_sum_waste_weight_kg ?? 0), 3, ',', ' ') }} kg</div></td>
                                <td data-label="Dernier calibrage">{{ optional($reception->calibrations_max_calibrated_at)->format('d/m/Y H:i') }}</td>
                                <td data-label="Actions"><a href="{{ route('calibrages.show', $reception) }}" class="text-sm font-semibold text-[var(--castaneas-bordeaux)]">Voir le détail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-stone-500">Aucun calibrage.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="surface-header">{{ $receptions->links() }}</div>
        </section>
    </div>
</x-app-layout>