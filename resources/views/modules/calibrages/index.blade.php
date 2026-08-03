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
                            <th>Calibre</th>
                            <th>Poids</th>
                            <th>Palox</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse ($calibrations as $calibration)
                            <tr>
                                <td data-label="Réception">{{ $calibration->reception->reception_number }}<div class="text-xs text-stone-500">{{ $calibration->reception->supplier->supplier_code }} - {{ $calibration->reception->variety->name }}</div></td>
                                <td data-label="Calibre">{{ $calibration->caliber->name }}</td>
                                <td data-label="Poids">{{ number_format((float) $calibration->net_weight_kg, 3, ',', ' ') }} kg<div class="text-xs text-stone-500">Déchet: {{ number_format((float) $calibration->waste_weight_kg, 3, ',', ' ') }} kg</div></td>
                                <td data-label="Palox">{{ $calibration->palox?->palox_number }}</td>
                                <td data-label="Actions">@if($calibration->palox)<a href="{{ route('paloxes.label', $calibration->palox) }}" class="text-sm font-semibold text-[var(--castaneas-bordeaux)]">Étiquette</a>@endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-stone-500">Aucun calibrage.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="surface-header">{{ $calibrations->links() }}</div>
        </section>
    </div>
</x-app-layout>