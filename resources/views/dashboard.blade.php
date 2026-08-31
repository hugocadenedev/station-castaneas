<x-app-layout :dashboard-dark="true" :flush-content="true">
    <div class="min-h-screen px-4 py-6 sm:px-5 2xl:px-8" style="background: radial-gradient(circle at top left, #4b3b30 0%, #2a211d 42%, #1f1916 100%);">
        <section class="overflow-hidden rounded-3xl text-white" style="border: 1px solid #3b2a21; background: rgba(31, 25, 22, 0.72); box-shadow: 0 24px 70px rgba(45, 29, 23, 0.28); backdrop-filter: blur(4px);">
            <div class="border-b border-white/10 px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Performance & pilotage</div>
                        <h3 class="mt-2 font-display text-4xl leading-tight text-white">Vue opérationnelle</h3>
                        <div class="mt-1 text-sm text-stone-300">Lecture immédiate des flux, alertes et raccourcis du jour.</div>
                    </div>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3 xl:flex xl:items-center">
                        <a href="{{ route('receptions.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/[0.12]">
                            <span class="text-lg leading-none">+</span> Nouvelle réception
                        </a>
                        <a href="{{ route('calibrages.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/[0.12]">
                            <span class="text-lg leading-none">+</span> Nouveau calibrage
                        </a>
                        <a href="{{ route('commandes.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold text-white transition hover:brightness-110" style="border: 1px solid #8b5a44; background: linear-gradient(135deg, #7d4a2d 0%, #5e3421 100%);">
                            <span class="text-lg leading-none">+</span> Nouvelle commande
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 px-5 py-5 sm:px-6 xl:grid-cols-[minmax(0,1.4fr)_360px]">
                <div class="space-y-5">
                    <div class="grid gap-4 xl:grid-cols-2">
                        <article class="rounded-3xl border border-white/10 p-5" style="background: #191311;">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-400">Stock disponible</div>
                                    <h4 class="mt-1 font-display text-2xl text-white">Par fruit</h4>
                                </div>
                                <a href="{{ route('stock.index') }}" class="text-sm font-semibold text-[#efc79c]">Voir le stock</a>
                            </div>
                            <div class="mt-5 space-y-4">
                                @forelse ($stockByFruit as $fruitName => $fruitData)
                                    <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="font-semibold text-white">{{ $fruitName }}</div>
                                            <div class="text-lg font-semibold text-white">{{ number_format($fruitData['total'], 0, ',', ' ') }} kg</div>
                                        </div>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach ($fruitData['calibers'] as $caliberName => $weight)
                                                <div class="inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-1.5 text-xs text-stone-200">
                                                    <span class="font-semibold text-[#efc79c]">{{ $caliberName }}</span>
                                                    <span>{{ number_format($weight, 0, ',', ' ') }} kg</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-2xl border border-dashed border-white/10 px-4 py-6 text-center text-sm text-stone-400">Aucun stock disponible pour le moment.</div>
                                @endforelse
                            </div>
                        </article>

                        <article class="rounded-3xl border border-white/10 p-5" style="background: #191311;">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-400">Stock vendu</div>
                                    <h4 class="mt-1 font-display text-2xl text-white">Par fruit</h4>
                                </div>
                                <a href="{{ route('commandes.index') }}" class="text-sm font-semibold text-[#efc79c]">Voir les commandes</a>
                            </div>
                            <div class="mt-5 space-y-4">
                                @forelse ($soldByFruit as $fruitName => $fruitData)
                                    <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="font-semibold text-white">{{ $fruitName }}</div>
                                            <div class="text-lg font-semibold text-white">{{ number_format($fruitData['total'], 0, ',', ' ') }} kg</div>
                                        </div>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach ($fruitData['calibers'] as $caliberName => $weight)
                                                <div class="inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-1.5 text-xs text-stone-200">
                                                    <span class="font-semibold text-[#efc79c]">{{ $caliberName }}</span>
                                                    <span>{{ number_format($weight, 0, ',', ' ') }} kg</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-2xl border border-dashed border-white/10 px-4 py-6 text-center text-sm text-stone-400">Aucune vente enregistrée pour le moment.</div>
                                @endforelse
                            </div>
                        </article>
                    </div>
                </div>

                <aside class="space-y-4 rounded-3xl border border-white/10 p-5" style="background: #16110f;">
                    <div>
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-400">Raccourcis modules</div>
                        <h4 class="mt-1 font-display text-2xl text-white">Navigation</h4>
                    </div>
                    <div class="grid gap-3">
                        <a href="{{ route('receptions.index') }}" class="rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-4 transition hover:bg-white/[0.08]">
                            <div class="text-sm font-semibold text-white">Réception</div>
                            <div class="mt-1 text-xs text-stone-400">Lots, fournisseurs, conformité</div>
                        </a>
                        <a href="{{ route('calibrages.index') }}" class="rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-4 transition hover:bg-white/[0.08]">
                            <div class="text-sm font-semibold text-white">Calibrage</div>
                            <div class="mt-1 text-xs text-stone-400">Pesées et création palox</div>
                        </a>
                        <a href="{{ route('stock.index') }}" class="rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-4 transition hover:bg-white/[0.08]">
                            <div class="text-sm font-semibold text-white">Stock</div>
                            <div class="mt-1 text-xs text-stone-400">Disponibilités et traçabilité</div>
                        </a>
                        <a href="{{ route('commandes.index') }}" class="rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-4 transition hover:bg-white/[0.08]">
                            <div class="text-sm font-semibold text-white">Commandes</div>
                            <div class="mt-1 text-xs text-stone-400">Préparation et suivi</div>
                        </a>
                        @if (auth()->user()->hasRole('superadmin'))
                            <a href="{{ route('backoffice.index') }}" class="rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-4 transition hover:bg-white/[0.08]">
                                <div class="text-sm font-semibold text-white">Backoffice</div>
                                <div class="mt-1 text-xs text-stone-400">Référentiels et utilisateurs</div>
                            </a>
                        @endif
                    </div>
                </aside>
            </div>
        </section>
    </div>
</x-app-layout>
