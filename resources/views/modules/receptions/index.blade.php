<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 2xl:flex-row 2xl:items-end 2xl:justify-between">
            <div>
                <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Module 1</div>
                <h1 class="font-display text-3xl leading-tight text-[var(--castaneas-ink)]">Réception</h1>
            </div>
            <a href="{{ route('receptions.create') }}" class="btn-primary">Nouvelle réception</a>
        </div>
    </x-slot>

    <x-flash-status />

    <div class="space-y-6">
        <section class="surface overflow-hidden rounded-2xl">
            <div class="toolbar">
                <form method="GET" class="toolbar-grid w-full">
                    <input type="text" name="reception_number" value="{{ request('reception_number') }}" placeholder="N° réception" class="input">
                    <select name="supplier_id" class="input">
                        <option value="">Tous les fournisseurs</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected((string) request('supplier_id') === (string) $supplier->id)>{{ $supplier->supplier_code }}</option>
                        @endforeach
                    </select>
                    <select name="fruit_id" class="input">
                        <option value="">Tous les fruits</option>
                        @foreach ($fruits as $fruit)
                            <option value="{{ $fruit->id }}" @selected((string) request('fruit_id') === (string) $fruit->id)>{{ $fruit->name }}</option>
                        @endforeach
                    </select>
                    <select name="variety_id" class="input">
                        <option value="">Toutes les variétés</option>
                        @foreach ($varieties as $variety)
                            <option value="{{ $variety->id }}" @selected((string) request('variety_id') === (string) $variety->id)>{{ $variety->name }}</option>
                        @endforeach
                    </select>
                    <select name="conformity_status" class="input">
                        <option value="">Tous les statuts</option>
                        <option value="conforming" @selected(request('conformity_status') === 'conforming')>Conforme</option>
                        <option value="non_conforming" @selected(request('conformity_status') === 'non_conforming')>Non conforme</option>
                    </select>
                    <div class="md:col-span-2 lg:col-span-3 2xl:col-span-5 flex flex-col gap-3 sm:flex-row">
                        <button class="btn-primary">Filtrer</button>
                        <a href="{{ route('receptions.index') }}" class="btn-secondary">Réinitialiser</a>
                    </div>
                </form>
            </div>
        </section>

        <section class="surface overflow-hidden rounded-2xl">
            <div class="overflow-x-auto">
                <table class="data-table tablet-stack">
                    <thead>
                        <tr>
                            <th>Réception</th>
                            <th>Fournisseur</th>
                            <th>Fruit</th>
                            <th>Variété</th>
                            <th>Poids</th>
                            <th>Statut</th>
                            <th>Opérateur</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse ($receptions as $reception)
                            <tr>
                                <td data-label="Réception"><div class="font-semibold text-stone-800">{{ $reception->reception_number }}</div><div class="text-xs text-stone-500">{{ $reception->received_at->format('d/m/Y H:i') }}</div></td>
                                <td data-label="Fournisseur">{{ $reception->supplier->supplier_code }}</td>
                                <td data-label="Fruit">{{ $reception->fruit->name }}</td>
                                <td data-label="Variété">{{ $reception->variety->name }}</td>
                                <td data-label="Poids">{{ number_format((float) $reception->gross_weight_kg, 3, ',', ' ') }} kg</td>
                                <td data-label="Statut">@if ($reception->conformity_status === 'conforming')<span class="pill pill-ok">Conforme</span>@else<span class="pill pill-alert">Non conforme</span>@endif</td>
                                <td data-label="Opérateur">{{ $reception->operator->name }}</td>
                                <td data-label="Actions">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('receptions.label', $reception) }}" class="text-sm font-semibold text-[var(--castaneas-bordeaux)]">Étiquette</a>
                                        @if ($reception->non_conformity_reason)
                                            <span class="text-xs text-stone-500">{{ $reception->non_conformity_reason }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-6 text-center text-stone-500">Aucune réception.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="surface-header">{{ $receptions->links() }}</div>
        </section>
    </div>
</x-app-layout>