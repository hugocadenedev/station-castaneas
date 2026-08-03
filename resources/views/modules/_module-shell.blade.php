@php
    $filters = $filters ?? [];
    $columns = $columns ?? [];
    $actions = $actions ?? [];
    $notes = $notes ?? [];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">{{ $eyebrow }}</div>
                <h1 class="font-display text-3xl leading-tight text-[var(--castaneas-ink)]">{{ $title }}</h1>
            </div>
            <div class="pill {{ $statusClass ?? 'pill-warn' }}">{{ $status }}</div>
        </div>
    </x-slot>

    <div class="shell space-y-8">
        <section class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
            <article class="panel p-6 sm:p-8">
                <p class="max-w-3xl text-sm leading-7 text-stone-600">{{ $description }}</p>

                <div class="mt-8 space-y-4">
                    <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Filtres à prévoir</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($filters as $filter)
                            <span class="filter-chip">{{ $filter }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="mt-8 overflow-hidden rounded-3xl border border-stone-200">
                    <div class="grid grid-cols-1 gap-px bg-stone-200 md:grid-cols-{{ max(count($columns), 1) }}">
                        @foreach ($columns as $column)
                            <div class="bg-stone-50 px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">{{ $column }}</div>
                        @endforeach
                    </div>
                    <div class="bg-white px-4 py-6 text-sm leading-6 text-stone-600">
                        Cette page sert de repère d’interface pour la V1. Les écrans dynamiques et les filtres métier seront branchés sur les modèles et services du domaine.
                    </div>
                </div>
            </article>

            <aside class="space-y-6">
                <section class="panel p-6">
                    <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Actions prévues</div>
                    <ul class="mt-4 space-y-3 text-sm leading-6 text-stone-700">
                        @foreach ($actions as $action)
                            <li>{{ $action }}</li>
                        @endforeach
                    </ul>
                </section>

                <section class="panel p-6">
                    <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Règles métier</div>
                    <ul class="mt-4 space-y-3 text-sm leading-6 text-stone-700">
                        @foreach ($notes as $note)
                            <li>{{ $note }}</li>
                        @endforeach
                    </ul>
                </section>
            </aside>
        </section>
    </div>
</x-app-layout>