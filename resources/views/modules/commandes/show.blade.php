<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 2xl:flex-row 2xl:items-end 2xl:justify-between">
            <div>
                <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Détail commande</div>
                <h1 class="font-display text-3xl leading-tight text-[var(--castaneas-ink)]">{{ $order->order_number }}</h1>
            </div>
            <a href="{{ route('commandes.index') }}" class="btn-secondary">Retour à la liste</a>
        </div>
    </x-slot>

    <div class="grid gap-6 2xl:grid-cols-2">
        <section class="surface rounded-2xl">
            <div class="surface-header">
                <h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Informations commande</h2>
            </div>
            <div class="surface-body grid gap-3 text-sm leading-6 text-stone-700 sm:grid-cols-2">
                <div><strong>Commande :</strong><div>{{ $order->order_number }}</div></div>
                <div><strong>Date :</strong><div>{{ $order->ordered_at->format('d/m/Y H:i') }}</div></div>
                <div><strong>Client :</strong><div>{{ $order->customer?->name ?? $order->client_name }}</div></div>
                <div><strong>Opérateur :</strong><div>{{ $order->operator->name }}</div></div>
                <div><strong>Poids total prélevé :</strong><div>{{ number_format((float) $order->paloxes->sum(fn ($palox) => (float) $palox->pivot->picked_net_weight_kg), 3, ',', ' ') }} kg</div></div>
            </div>
        </section>

        <section class="surface rounded-2xl">
            <div class="surface-header">
                <h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Synthèse palox</h2>
            </div>
            <div class="surface-body">
                <ul class="space-y-3 text-sm leading-6 text-stone-700">
                    @forelse ($order->paloxes as $palox)
                        <li class="border-b border-stone-100 pb-3 last:border-b-0 last:pb-0">
                            <strong>{{ $palox->palox_number }}</strong> - {{ $palox->reception->fruit->name }} / {{ $palox->reception->variety->name }}
                            <div class="text-xs text-stone-500">Prélevé : {{ number_format((float) $palox->pivot->picked_net_weight_kg, 3, ',', ' ') }} kg - Reste : {{ number_format((float) $palox->remaining_net_weight_kg, 3, ',', ' ') }} kg</div>
                        </li>
                    @empty
                        <li>Aucun palox lié à cette commande.</li>
                    @endforelse
                </ul>
            </div>
        </section>

        <section class="surface rounded-2xl 2xl:col-span-2">
            <div class="surface-header">
                <h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Détail des palox prélevés</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table tablet-stack">
                    <thead>
                        <tr>
                            <th>Palox</th>
                            <th>Fournisseur</th>
                            <th>Fruit</th>
                            <th>Variété</th>
                            <th>Calibre</th>
                            <th>Prélevé</th>
                            <th>Reste</th>
                            <th>Certifié</th>
                            <th>État</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse ($order->paloxes as $palox)
                            <tr>
                                <td data-label="Palox" class="font-semibold text-stone-800">{{ $palox->palox_number }}</td>
                                <td data-label="Fournisseur">{{ $palox->reception->supplier->supplier_code }}</td>
                                <td data-label="Fruit">{{ $palox->reception->fruit->name }}</td>
                                <td data-label="Variété">{{ $palox->reception->variety->name }}</td>
                                <td data-label="Calibre">{{ $palox->calibration->caliber->name }}</td>
                                <td data-label="Prélevé">{{ number_format((float) $palox->pivot->picked_net_weight_kg, 3, ',', ' ') }} kg</td>
                                <td data-label="Reste">{{ number_format((float) $palox->remaining_net_weight_kg, 3, ',', ' ') }} kg</td>
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
                                    @else
                                        <span class="pill pill-alert">Épuisé</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-4 py-6 text-center text-stone-500">Aucun palox lié à cette commande.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>