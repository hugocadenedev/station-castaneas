<x-app-layout :dashboard-dark="true" :flush-content="true">
    @php
        $activityScore = $stats['receptions_today'] + $stats['calibrations_today'] + $stats['orders_today'];
        $alertCount = $stats['pending_receptions'] + $stats['non_conforming_receptions'];
    @endphp

    <div class="min-h-screen px-4 py-6 sm:px-5 2xl:px-8" style="background: radial-gradient(circle at top left, #4b3b30 0%, #2a211d 42%, #1f1916 100%);">
        <section class="overflow-hidden rounded-3xl text-white" style="border: 1px solid #3b2a21; background: rgba(31, 25, 22, 0.72); box-shadow: 0 24px 70px rgba(45, 29, 23, 0.28); backdrop-filter: blur(4px);">
            <div class="border-b border-white/10 px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Performance & pilotage</div>
                        <h3 class="mt-2 font-display text-4xl leading-tight text-white">Vue opérationnelle</h3>
                        <div class="mt-1 text-sm text-stone-300">Lecture immédiate des flux, alertes et raccourcis du jour.</div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 px-5 py-5 sm:px-6 xl:grid-cols-[minmax(0,1.4fr)_360px]">
                <div class="space-y-5">
                    <div class="flex flex-wrap gap-3 rounded-2xl p-3 text-sm" style="border: 1px solid #8b5a44; background: #1b1412;">
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-1.5 text-stone-200">
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-md px-1.5 text-xs font-bold text-white" style="background: #ff5d4d;">{{ $stats['pending_receptions'] }}</span>
                            Réceptions à calibrer
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-1.5 text-stone-200">
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-md px-1.5 text-xs font-bold text-white" style="background: #ff8a2b;">{{ $stats['orders_today'] }}</span>
                            Commandes du jour
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/5 px-3 py-1.5 text-stone-200">
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-md px-1.5 text-xs font-bold" style="background: #e0ac31; color: #281b14;">{{ $stats['non_conforming_receptions'] }}</span>
                            Non conformes
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <article class="rounded-3xl border border-white/10 p-4 backdrop-blur-sm" style="background: rgba(255,255,255,0.06);">
                            <div class="flex items-start justify-between gap-3">
                                <div class="rounded-2xl bg-emerald-500/10 p-2 text-emerald-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 17 17 7"/><path d="M9 7h8v8"/></svg>
                                </div>
                                <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-400">Réceptions</div>
                            </div>
                            <div class="mt-6 text-5xl font-semibold text-white">{{ $stats['receptions_today'] }}</div>
                            <div class="mt-2 text-sm text-stone-300">{{ $stats['pending_receptions'] }} en attente</div>
                        </article>
                        <article class="rounded-3xl border border-white/10 p-4 backdrop-blur-sm" style="background: rgba(255,255,255,0.06);">
                            <div class="flex items-start justify-between gap-3">
                                <div class="rounded-2xl bg-sky-500/10 p-2 text-sky-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16"/><path d="M7 4v6"/><path d="M17 4v6"/><rect x="4" y="7" width="16" height="13" rx="2"/></svg>
                                </div>
                                <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-400">Calibrages</div>
                            </div>
                            <div class="mt-6 text-5xl font-semibold text-white">{{ $stats['calibrations_today'] }}</div>
                            <div class="mt-2 text-sm text-stone-300">Traitements réalisés aujourd’hui</div>
                        </article>
                        <article class="rounded-3xl border border-white/10 p-4 backdrop-blur-sm" style="background: rgba(255,255,255,0.06);">
                            <div class="flex items-start justify-between gap-3">
                                <div class="rounded-2xl bg-amber-500/10 p-2 text-amber-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18"/><path d="M7 6v12"/><path d="M17 6v12"/><rect x="5" y="4" width="14" height="16" rx="2"/></svg>
                                </div>
                                <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-400">Stock</div>
                            </div>
                            <div class="mt-6 text-5xl font-semibold text-white">{{ $stats['palox_in_stock'] }}</div>
                            <div class="mt-2 text-sm text-stone-300">palox disponibles</div>
                        </article>
                        <article class="rounded-3xl border border-white/10 p-4 backdrop-blur-sm" style="background: rgba(255,255,255,0.06);">
                            <div class="flex items-start justify-between gap-3">
                                <div class="rounded-2xl bg-violet-500/10 p-2 text-violet-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="19" r="1"/><circle cx="17" cy="19" r="1"/><path d="M3 4h2l2.4 10.2a1 1 0 0 0 1 .8h8.7a1 1 0 0 0 1-.7L21 7H7"/></svg>
                                </div>
                                <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-400">Commandes</div>
                            </div>
                            <div class="mt-6 text-5xl font-semibold text-white">{{ $stats['orders_today'] }}</div>
                            <div class="mt-2 text-sm text-stone-300">{{ number_format($stats['picked_today_kg'], 0, ',', ' ') }} kg sortis</div>
                        </article>
                    </div>

                    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(290px,0.8fr)]">
                        <article class="rounded-3xl border border-white/10 p-5" style="background: #191311;">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-400">Capacité disponible</div>
                                    <h4 class="mt-1 font-display text-2xl text-white">Stock prêt au prélèvement</h4>
                                </div>
                                <a href="{{ route('stock.index') }}" class="text-sm font-semibold text-[#efc79c]">Voir le stock</a>
                            </div>
                            <div class="mt-6 grid gap-4 sm:grid-cols-[minmax(0,1fr)_140px] sm:items-end">
                                <div>
                                    <div class="text-5xl font-semibold text-white">{{ number_format($stats['stock_weight_kg'], 0, ',', ' ') }}<span class="ml-2 text-2xl text-stone-400">kg</span></div>
                                    <div class="mt-3 h-3 overflow-hidden rounded-full bg-white/10">
                                        <div class="h-full rounded-full" style="width: {{ min(100, max(12, $activityScore * 8)) }}%; background: linear-gradient(90deg, #d97d22 0%, #f1c27d 100%);"></div>
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-4 text-right">
                                    <div class="text-[11px] uppercase tracking-[0.18em] text-stone-400">Flux jour</div>
                                    <div class="mt-2 text-3xl font-semibold text-white">{{ $activityScore }}</div>
                                </div>
                            </div>
                        </article>

                        <article class="rounded-3xl border border-white/10 p-5" style="background: #191311;">
                            <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-400">Création rapide</div>
                            <div class="mt-4 grid gap-3">
                                <a href="{{ route('receptions.create') }}" class="group rounded-2xl border border-white/10 bg-white/[0.05] px-4 py-3 transition hover:bg-white/[0.08]">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="font-semibold text-white">Nouvelle réception</div>
                                            <div class="mt-1 text-sm text-stone-400">Arrivée fournisseur</div>
                                        </div>
                                        <div class="text-xl text-stone-500 transition group-hover:text-[#efc79c]">+</div>
                                    </div>
                                </a>
                                <a href="{{ route('calibrages.create') }}" class="group rounded-2xl border border-white/10 bg-white/[0.05] px-4 py-3 transition hover:bg-white/[0.08]">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="font-semibold text-white">Nouveau calibrage</div>
                                            <div class="mt-1 text-sm text-stone-400">Transformation palox</div>
                                        </div>
                                        <div class="text-xl text-stone-500 transition group-hover:text-[#efc79c]">+</div>
                                    </div>
                                </a>
                                <a href="{{ route('commandes.create') }}" class="group rounded-2xl px-4 py-3 transition hover:brightness-110" style="border: 1px solid #8b5a44; background: linear-gradient(135deg, #7d4a2d 0%, #5e3421 100%);">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="font-semibold text-white">Nouvelle commande</div>
                                            <div class="mt-1 text-sm text-stone-200">Prélever et expédier</div>
                                        </div>
                                        <div class="text-xl text-white">+</div>
                                    </div>
                                </a>
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
