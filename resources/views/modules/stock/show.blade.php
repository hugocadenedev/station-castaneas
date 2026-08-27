<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Traçabilité palox</div>
            <h1 class="font-display text-3xl leading-tight text-[var(--castaneas-ink)]">{{ $palox->palox_number }}</h1>
        </div>
    </x-slot>

    <x-flash-status />

    <div class="grid gap-6 2xl:grid-cols-2">
        <section class="surface rounded-2xl">
            <div class="surface-header">
                <h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Origine</h2>
            </div>
            <div class="surface-body grid gap-3 text-sm leading-6 text-stone-700 sm:grid-cols-2">
                <div><strong>Réception :</strong><div>{{ $palox->reception?->reception_number ?? 'Indisponible' }}</div></div>
                <div><strong>ID fournisseur :</strong><div>{{ $palox->reception?->supplier?->supplier_code ?? 'Indisponible' }}</div></div>
                <div><strong>Fruit / Variété :</strong><div>{{ $palox->reception?->fruit?->name ?? 'Indisponible' }} / {{ $palox->reception?->variety?->name ?? 'Indisponible' }}</div></div>
                <div><strong>Opérateur réception :</strong><div>{{ $palox->reception?->operator?->name ?? 'Indisponible' }}</div></div>
                <div><strong>Calibre :</strong><div>{{ $palox->calibration?->caliber?->name ?? 'Sans calibre (déchet)' }}</div></div>
                <div><strong>Tare :</strong><div>{{ $palox->calibration ? number_format((float) $palox->calibration->tare_weight_kg, 3, ',', ' ').' kg' : 'Indisponible' }}</div></div>
                <div><strong>Opérateur calibrage :</strong><div>{{ $palox->calibration?->operator?->name ?? 'Indisponible' }}</div></div>
                <div><strong>État :</strong><div>{{ $palox->availability_status === 'reserved' ? 'Réservé' : ($palox->availability_status === 'partial' ? 'Partiel' : ($palox->availability_status === 'exhausted' ? 'Épuisé' : 'Disponible')) }}</div></div>
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
                        <div class="text-xs text-stone-500">Prélevé: {{ number_format((float) $order->pivot->picked_net_weight_kg, 3, ',', ' ') }} kg par {{ $order->operator?->name ?? 'Utilisateur indisponible' }}</div>
                    </li>
                @empty
                    <li>Aucune commande liée pour le moment.</li>
                @endforelse
            </ul>
            </div>
        </section>
    </div>

    @if (auth()->user()->hasRole('superadmin'))
        <section class="mt-6 surface rounded-2xl">
            <div class="surface-body flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-display text-xl text-[var(--castaneas-ink)]">Réservation</h2>
                    <p class="mt-1 text-sm text-stone-600">Un palox réservé n'est pas proposé lors de la création d'une commande.</p>
                </div>
                @if ($palox->availability_status === 'reserved')
                    <form method="POST" action="{{ route('stock.reservation.update', $palox) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="reserved" value="0">
                        <button class="btn-secondary">Rendre disponible</button>
                    </form>
                @elseif ((float) $palox->remaining_net_weight_kg > 0)
                    <form method="POST" action="{{ route('stock.reservation.update', $palox) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="reserved" value="1">
                        <button class="btn-primary">Réserver</button>
                    </form>
                @endif
            </div>
        </section>
    @endif
</x-app-layout>