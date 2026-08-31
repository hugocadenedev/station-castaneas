<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 2xl:flex-row 2xl:items-end 2xl:justify-between">
            <div>
                <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Module 4</div>
                <h1 class="font-display text-3xl leading-tight text-[var(--castaneas-ink)]">Commandes</h1>
            </div>
            <a href="{{ route('commandes.create') }}" class="btn-primary">Nouvelle commande</a>
        </div>
    </x-slot>

    <x-flash-status />

    <div class="space-y-6">
        <section class="surface overflow-hidden rounded-2xl">
            <div class="toolbar">
                <form method="GET" class="grid gap-3 md:grid-cols-[1fr_auto] w-full">
                        <input type="text" name="order_number" value="{{ request('order_number') }}" placeholder="Numéro de commande" class="input">
                        <button class="btn-primary">Filtrer</button>
                    </form>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table tablet-stack">
                    <thead>
                        <tr>
                            <th>Commande</th>
                            <th>Palox</th>
                            <th>Poids prélevé</th>
                            <th>Opérateur</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 bg-white">
                        @forelse ($orders as $order)
                            <tr>
                                <td data-label="Commande" class="font-semibold text-stone-800">
                                    <a href="{{ route('commandes.show', $order) }}" class="text-[var(--castaneas-bordeaux)] hover:text-[var(--castaneas-ink)]">{{ $order->order_number }}</a>
                                    <div class="text-xs text-stone-500">{{ $order->customer?->name ?? $order->client_name }} - {{ $order->ordered_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td data-label="Palox">
                                    @foreach ($order->paloxes as $palox)
                                        <div>{{ $palox->palox_number }} - {{ $palox->reception->variety->name }}</div>
                                    @endforeach
                                </td>
                                <td data-label="Poids prélevé">
                                    @foreach ($order->paloxes as $palox)
                                        <div>{{ number_format((float) $palox->pivot->picked_net_weight_kg, 3, ',', ' ') }} kg</div>
                                    @endforeach
                                </td>
                                <td data-label="Opérateur">{{ $order->operator->name }}</td>
                                <td data-label="Actions">
                                    <div class="flex flex-col gap-2">
                                        <a href="{{ route('commandes.show', $order) }}" class="text-sm font-semibold text-[var(--castaneas-bordeaux)]">Voir le détail</a>
                                        <a href="{{ route('commandes.edit', $order) }}" class="text-sm font-semibold text-[var(--castaneas-bordeaux)]">Modifier le numéro</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-stone-500">Aucune commande.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="surface-header">{{ $orders->links() }}</div>
        </section>
    </div>
</x-app-layout>
