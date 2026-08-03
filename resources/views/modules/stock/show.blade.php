<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Traçabilité palox</div>
            <h1 class="font-display text-3xl leading-tight text-[var(--castaneas-ink)]">{{ $palox->palox_number }}</h1>
        </div>
    </x-slot>

    <div class="grid gap-6 2xl:grid-cols-2">
        <section class="surface rounded-2xl">
            <div class="surface-header">
                <h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Origine</h2>
            </div>
            <div class="surface-body grid gap-3 text-sm leading-6 text-stone-700 sm:grid-cols-2">
                <div><strong>Réception :</strong><div>{{ $palox->reception->reception_number }}</div></div>
                <div><strong>ID fournisseur :</strong><div>{{ $palox->reception->supplier->supplier_code }}</div></div>
                <div><strong>Fruit / Variété :</strong><div>{{ $palox->reception->fruit->name }} / {{ $palox->reception->variety->name }}</div></div>
                <div><strong>Opérateur réception :</strong><div>{{ $palox->reception->operator->name }}</div></div>
                <div><strong>Calibre :</strong><div>{{ $palox->calibration->caliber->name }}</div></div>
                <div><strong>Tare :</strong><div>{{ number_format((float) $palox->calibration->tare_weight_kg, 3, ',', ' ') }} kg</div></div>
                <div><strong>Opérateur calibrage :</strong><div>{{ $palox->calibration->operator->name }}</div></div>
            </div>
        </section>

        <section class="surface rounded-2xl">
            <div class="surface-header">
                <h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Commandes liées</h2>
            </div>
            <div class="surface-body">
            <ul class="space-y-3 text-sm leading-6 text-stone-700">
                @forelse ($palox->orders as $order)
                    <li class="border-b border-stone-100 pb-3 last:border-b-0 last:pb-0">
                        <strong>{{ $order->order_number }}</strong> - {{ $order->client_name }}
                        <div class="text-xs text-stone-500">Prélevé: {{ number_format((float) $order->pivot->picked_net_weight_kg, 3, ',', ' ') }} kg par {{ $order->operator->name }}</div>
                    </li>
                @empty
                    <li>Aucune commande liée pour le moment.</li>
                @endforelse
            </ul>
            </div>
        </section>
    </div>
</x-app-layout>