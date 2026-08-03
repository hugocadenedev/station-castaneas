<nav x-data="{ open: false }">
    <aside class="app-sidebar">
        <div class="px-5 py-5">
            <a href="{{ route('dashboard') }}" class="flex flex-col gap-1">
                <span class="text-[11px] font-semibold uppercase tracking-[0.28em] text-stone-400">Castaneas</span>
                <span class="font-display text-2xl text-white">Station</span>
            </a>
        </div>

        <div class="app-sidebar-section">
            <div class="mb-3 text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-500">Navigation</div>
            <div class="space-y-1">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Tableau de bord</x-nav-link>
                <x-nav-link :href="route('receptions.index')" :active="request()->routeIs('receptions.*')">Réception</x-nav-link>
                <x-nav-link :href="route('calibrages.index')" :active="request()->routeIs('calibrages.*')">Calibrage</x-nav-link>
                <x-nav-link :href="route('stock.index')" :active="request()->routeIs('stock.*')">Stock</x-nav-link>
                <x-nav-link :href="route('commandes.index')" :active="request()->routeIs('commandes.*')">Commandes</x-nav-link>
                @if (Auth::user()?->hasRole('superadmin'))
                    <x-nav-link :href="route('backoffice.index')" :active="request()->routeIs('backoffice.*')">Backoffice</x-nav-link>
                @endif
            </div>
        </div>

        <div class="mt-auto app-sidebar-section">
            <div class="rounded-xl bg-white/5 p-4">
                <div class="text-sm font-semibold text-white">{{ Auth::user()->name }}</div>
                <div class="mt-1 text-xs uppercase tracking-[0.18em] text-stone-400">{{ Auth::user()->hasRole('superadmin') ? 'Superadmin' : 'Opérateur' }}</div>
                <div class="mt-4 space-y-1">
                    <a href="{{ route('profile.edit') }}" class="sidebar-link">Mon profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="sidebar-link w-full text-left">Déconnexion</button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <div class="app-topbar 2xl:hidden">
        <div class="content-shell py-3">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('dashboard') }}" class="flex flex-col">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.28em] text-stone-500">Castaneas</span>
                    <span class="font-display text-2xl text-[var(--castaneas-brown)]">Station</span>
                </a>
                <button @click="open = true" class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-stone-300 bg-white px-3 text-sm font-semibold text-stone-700">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span>Menu</span>
                </button>
            </div>
        </div>
    </div>

    <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-40 2xl:hidden">
        <div class="absolute inset-0 bg-stone-950/40" @click="open = false"></div>
        <aside x-transition:enter="transform transition ease-out duration-200"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="absolute inset-y-0 left-0 flex w-[min(24rem,88vw)] flex-col bg-[#1f1a17] text-stone-200 shadow-2xl">
            <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                <a href="{{ route('dashboard') }}" class="flex flex-col gap-1">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.28em] text-stone-400">Castaneas</span>
                    <span class="font-display text-2xl text-white">Station</span>
                </a>
                <button @click="open = false" class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-white">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="app-sidebar-section">
                <div class="mb-3 text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-500">Navigation</div>
                <div class="space-y-1.5">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Tableau de bord</x-nav-link>
                    <x-nav-link :href="route('receptions.index')" :active="request()->routeIs('receptions.*')">Réception</x-nav-link>
                    <x-nav-link :href="route('calibrages.index')" :active="request()->routeIs('calibrages.*')">Calibrage</x-nav-link>
                    <x-nav-link :href="route('stock.index')" :active="request()->routeIs('stock.*')">Stock</x-nav-link>
                    <x-nav-link :href="route('commandes.index')" :active="request()->routeIs('commandes.*')">Commandes</x-nav-link>
                    @if (Auth::user()?->hasRole('superadmin'))
                        <x-nav-link :href="route('backoffice.index')" :active="request()->routeIs('backoffice.*')">Backoffice</x-nav-link>
                    @endif
                </div>
            </div>

            <div class="mt-auto app-sidebar-section">
                <div class="rounded-xl bg-white/5 p-4">
                    <div class="text-sm font-semibold text-white">{{ Auth::user()->name }}</div>
                    <div class="mt-1 text-xs text-stone-400">{{ Auth::user()->email }}</div>
                    <div class="mt-4 space-y-1">
                        <a href="{{ route('profile.edit') }}" class="sidebar-link">Mon profil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="sidebar-link w-full text-left">Déconnexion</button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</nav>
