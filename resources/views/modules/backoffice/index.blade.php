@php($section = request('section', 'clients'))

<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-sm font-semibold uppercase tracking-[0.24em] text-stone-500">Module 5</div>
            <h1 class="font-display text-3xl leading-tight text-[var(--castaneas-ink)]">Paramètres et référentiels</h1>
        </div>
    </x-slot>

    <x-flash-status />

    <div class="space-y-6">
        <section class="surface rounded-2xl">
            <div class="surface-body space-y-4">
                <div class="grid gap-3 md:grid-cols-2 2xl:grid-cols-5">
                    <a href="{{ route('backoffice.index', ['section' => 'clients']) }}" class="{{ $section === 'clients' ? 'btn-primary' : 'btn-secondary' }}">Clients</a>
                    <a href="{{ route('backoffice.index', ['section' => 'fournisseurs']) }}" class="{{ $section === 'fournisseurs' ? 'btn-primary' : 'btn-secondary' }}">Fournisseurs</a>
                    <a href="{{ route('backoffice.index', ['section' => 'production']) }}" class="{{ $section === 'production' ? 'btn-primary' : 'btn-secondary' }}">Production</a>
                    <a href="{{ route('backoffice.index', ['section' => 'utilisateurs']) }}" class="{{ $section === 'utilisateurs' ? 'btn-primary' : 'btn-secondary' }}">Utilisateurs</a>
                    <a href="{{ route('backoffice.index', ['section' => 'audit']) }}" class="{{ $section === 'audit' ? 'btn-primary' : 'btn-secondary' }}">Audit</a>
                </div>
                <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-4 text-sm text-stone-700">
                    <div class="border border-stone-200 bg-stone-50 p-4"><div class="text-xs uppercase tracking-[0.18em] text-stone-500">Clients</div><div class="mt-2 text-2xl font-semibold text-stone-900">{{ $customers->count() }}</div></div>
                    <div class="border border-stone-200 bg-stone-50 p-4"><div class="text-xs uppercase tracking-[0.18em] text-stone-500">Fournisseurs</div><div class="mt-2 text-2xl font-semibold text-stone-900">{{ $suppliers->count() }}</div></div>
                    <div class="border border-stone-200 bg-stone-50 p-4"><div class="text-xs uppercase tracking-[0.18em] text-stone-500">Références production</div><div class="mt-2 text-2xl font-semibold text-stone-900">{{ $fruits->count() + $fruits->sum(fn ($fruit) => $fruit->varieties->count()) + $calibers->count() + $tareTypes->count() }}</div></div>
                    <div class="border border-stone-200 bg-stone-50 p-4"><div class="text-xs uppercase tracking-[0.18em] text-stone-500">Utilisateurs</div><div class="mt-2 text-2xl font-semibold text-stone-900">{{ $users->count() }}</div></div>
                </div>
            </div>
        </section>

        @if ($section === 'clients')
            <section class="grid gap-6 2xl:grid-cols-[420px_minmax(0,1fr)]">
                <article class="surface rounded-2xl">
                    <div class="surface-header"><h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Nouveau client</h2></div>
                    <div class="surface-body">
                        <form method="POST" action="{{ route('backoffice.customers.store') }}" class="space-y-4">
                            @csrf
                            <input type="text" name="name" placeholder="Nom du client" class="input w-full" required>
                            <input type="text" name="reference_code" placeholder="Code GGN client (saisie manuelle)" class="input w-full">
                            <input type="text" name="contact_name" placeholder="Contact" class="input w-full">
                            <input type="email" name="email" placeholder="E-mail" class="input w-full">
                            <input type="text" name="phone" placeholder="Téléphone" class="input w-full">
                            <textarea name="notes" rows="4" placeholder="Informations utiles" class="textarea w-full"></textarea>
                            <button class="btn-primary">Ajouter le client</button>
                        </form>
                    </div>
                </article>

                <article class="surface overflow-hidden rounded-2xl">
                    <div class="surface-header flex items-center justify-between gap-3">
                        <h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Liste clients</h2>
                        <div class="text-sm text-stone-500">Référentiel commercial utilisé par les commandes</div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="data-table tablet-stack">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Code GGN</th>
                                    <th>Contact</th>
                                    <th>Coordonnées</th>
                                    <th>Notes</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 bg-white">
                                @forelse ($customers as $customer)
                                    <tr>
                                        <form id="customer-update-{{ $customer->id }}" method="POST" action="{{ route('backoffice.customers.update', $customer) }}">
                                            @csrf
                                            @method('PATCH')
                                            <td data-label="Client"><input type="text" name="name" value="{{ $customer->name }}" class="input w-full" required></td>
                                            <td data-label="Code GGN"><input type="text" name="reference_code" value="{{ $customer->reference_code }}" class="input w-full"></td>
                                            <td data-label="Contact"><input type="text" name="contact_name" value="{{ $customer->contact_name }}" class="input w-full"></td>
                                            <td data-label="Coordonnées" class="space-y-2">
                                                <input type="email" name="email" value="{{ $customer->email }}" placeholder="E-mail" class="input w-full">
                                                <input type="text" name="phone" value="{{ $customer->phone }}" placeholder="Téléphone" class="input w-full">
                                            </td>
                                            <td data-label="Notes"><textarea name="notes" rows="3" class="textarea w-full">{{ $customer->notes }}</textarea></td>
                                            <td data-label="Statut">
                                                <label class="inline-flex items-center gap-2 text-sm text-stone-700">
                                                    <input type="checkbox" name="is_active" value="1" class="rounded border-stone-300 text-[var(--castaneas-brown)] focus:ring-[var(--castaneas-brown)]" @checked($customer->is_active)>
                                                    Actif
                                                </label>
                                                <div class="mt-2 text-xs text-stone-500">{{ $customer->orders_count }} commande(s)</div>
                                            </td>
                                        </form>
                                        <td data-label="Actions">
                                            <div class="flex flex-col gap-2">
                                                <button form="customer-update-{{ $customer->id }}" class="btn-primary">Enregistrer</button>
                                                <form method="POST" action="{{ route('backoffice.customers.destroy', $customer) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn-danger w-full" onclick="return confirm('Supprimer ce client ?');">Supprimer</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="px-4 py-6 text-center text-stone-500">Aucun client référencé.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        @elseif ($section === 'fournisseurs')
            <section class="grid gap-6 2xl:grid-cols-[420px_minmax(0,1fr)]">
                <article class="surface rounded-2xl">
                    <div class="surface-header"><h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Nouveau fournisseur</h2></div>
                    <div class="surface-body">
                        <form method="POST" action="{{ route('backoffice.suppliers.store') }}" class="space-y-4">
                            @csrf
                            <input type="text" name="name" placeholder="Nom" class="input w-full" required>
                            <input type="text" name="ggn_code" placeholder="Code GGN" class="input w-full" required>
                            <input type="email" name="email" placeholder="E-mail" class="input w-full">
                            <input type="text" name="phone" placeholder="Téléphone" class="input w-full">
                            <button class="btn-primary">Ajouter le fournisseur</button>
                        </form>
                    </div>
                </article>

                <article class="surface overflow-hidden rounded-2xl">
                    <div class="surface-header flex items-center justify-between gap-3">
                        <h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Liste fournisseurs</h2>
                        <div class="text-sm text-stone-500">Référentiel réception et traçabilité GGN</div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="data-table tablet-stack">
                            <thead>
                                <tr>
                                    <th>Fournisseur</th>
                                    <th>GGN</th>
                                    <th>Coordonnées</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 bg-white">
                                @forelse ($suppliers as $supplier)
                                    <tr>
                                        <form id="supplier-update-{{ $supplier->id }}" method="POST" action="{{ route('backoffice.suppliers.update', $supplier) }}">
                                            @csrf
                                            @method('PATCH')
                                            <td data-label="Fournisseur"><input type="text" name="name" value="{{ $supplier->name }}" class="input w-full" required></td>
                                            <td data-label="GGN"><input type="text" name="ggn_code" value="{{ $supplier->ggn_code }}" class="input w-full" required></td>
                                            <td data-label="Coordonnées" class="space-y-2">
                                                <input type="email" name="email" value="{{ $supplier->email }}" placeholder="E-mail" class="input w-full">
                                                <input type="text" name="phone" value="{{ $supplier->phone }}" placeholder="Téléphone" class="input w-full">
                                            </td>
                                            <td data-label="Statut">
                                                <label class="inline-flex items-center gap-2 text-sm text-stone-700">
                                                    <input type="checkbox" name="is_active" value="1" class="rounded border-stone-300 text-[var(--castaneas-brown)] focus:ring-[var(--castaneas-brown)]" @checked($supplier->is_active)>
                                                    Actif
                                                </label>
                                                <div class="mt-2 text-xs text-stone-500">{{ $supplier->receptions_count }} reception(s)</div>
                                            </td>
                                        </form>
                                        <td data-label="Actions">
                                            <div class="flex flex-col gap-2">
                                                <button form="supplier-update-{{ $supplier->id }}" class="btn-primary">Enregistrer</button>
                                                <form method="POST" action="{{ route('backoffice.suppliers.destroy', $supplier) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn-danger w-full" onclick="return confirm('Supprimer ce fournisseur ?');">Supprimer</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-4 py-6 text-center text-stone-500">Aucun fournisseur enregistré.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        @elseif ($section === 'production')
            <section class="grid gap-6 2xl:grid-cols-2">
                <article class="surface rounded-2xl">
                    <div class="surface-header"><h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Fruits et variétés</h2></div>
                    <div class="surface-body grid gap-6 lg:grid-cols-2">
                        <form method="POST" action="{{ route('backoffice.fruits.store') }}" class="space-y-3 border border-stone-200 p-4">
                            @csrf
                            <div class="font-semibold text-stone-800">Ajouter un fruit</div>
                            <input type="text" name="name" placeholder="Nom du fruit" class="input w-full" required>
                            <button class="btn-primary">Ajouter</button>
                        </form>
                        <form method="POST" action="{{ route('backoffice.varieties.store') }}" class="space-y-3 border border-stone-200 p-4">
                            @csrf
                            <div class="font-semibold text-stone-800">Ajouter une variété</div>
                            <select name="fruit_id" class="input w-full" required>
                                <option value="">Fruit</option>
                                @foreach ($fruits as $fruit)
                                    <option value="{{ $fruit->id }}">{{ $fruit->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="name" placeholder="Nom de la variété" class="input w-full" required>
                            <button class="btn-primary">Ajouter</button>
                        </form>
                        <div class="border border-stone-200 p-4 lg:col-span-2">
                            <div class="font-semibold text-stone-800">Liste fruits</div>
                            <div class="mt-4 overflow-x-auto">
                                <table class="data-table tablet-stack">
                                    <thead>
                                        <tr>
                                            <th>Fruit</th>
                                            <th>Statut</th>
                                            <th>Usage</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-stone-100 bg-white">
                                        @forelse ($fruits as $fruit)
                                            <tr>
                                                <form id="fruit-update-{{ $fruit->id }}" method="POST" action="{{ route('backoffice.fruits.update', $fruit) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <td data-label="Fruit"><input type="text" name="name" value="{{ $fruit->name }}" class="input w-full" required></td>
                                                    <td data-label="Statut">
                                                        <label class="inline-flex items-center gap-2 text-sm text-stone-700">
                                                            <input type="checkbox" name="is_active" value="1" class="rounded border-stone-300 text-[var(--castaneas-brown)] focus:ring-[var(--castaneas-brown)]" @checked($fruit->is_active)>
                                                            Actif
                                                        </label>
                                                    </td>
                                                    <td data-label="Usage" class="text-xs text-stone-500">{{ $fruit->varieties_count }} variété(s) · {{ $fruit->calibers_count }} calibre(s) · {{ $fruit->receptions_count }} reception(s)</td>
                                                </form>
                                                <td data-label="Actions">
                                                    <div class="flex flex-col gap-2">
                                                        <button form="fruit-update-{{ $fruit->id }}" class="btn-secondary">Enregistrer</button>
                                                        <form method="POST" action="{{ route('backoffice.fruits.destroy', $fruit) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn-danger w-full" onclick="return confirm('Supprimer ce fruit ?');">Supprimer</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="px-4 py-6 text-center text-stone-500">Aucun fruit référencé.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="border border-stone-200 p-4 lg:col-span-2">
                            <div class="font-semibold text-stone-800">Liste variétés</div>
                            <div class="mt-4 overflow-x-auto">
                                <table class="data-table tablet-stack">
                                    <thead>
                                        <tr>
                                            <th>Fruit</th>
                                            <th>Variété</th>
                                            <th>Statut</th>
                                            <th>Usage</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-stone-100 bg-white">
                                        @php($hasVarieties = $fruits->sum(fn ($fruit) => $fruit->varieties->count()) > 0)
                                        @forelse ($fruits as $fruit)
                                            @foreach ($fruit->varieties as $variety)
                                                <tr>
                                                    <form id="variety-update-{{ $variety->id }}" method="POST" action="{{ route('backoffice.varieties.update', $variety) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <td data-label="Fruit">
                                                            <select name="fruit_id" class="input w-full" required>
                                                                @foreach ($fruits as $fruitOption)
                                                                    <option value="{{ $fruitOption->id }}" @selected($variety->fruit_id === $fruitOption->id)>{{ $fruitOption->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td data-label="Variété"><input type="text" name="name" value="{{ $variety->name }}" class="input w-full" required></td>
                                                        <td data-label="Statut">
                                                            <label class="inline-flex items-center gap-2 text-sm text-stone-700">
                                                                <input type="checkbox" name="is_active" value="1" class="rounded border-stone-300 text-[var(--castaneas-brown)] focus:ring-[var(--castaneas-brown)]" @checked($variety->is_active)>
                                                                Active
                                                            </label>
                                                        </td>
                                                        <td data-label="Usage" class="text-xs text-stone-500">{{ $variety->receptions()->count() }} reception(s)</td>
                                                    </form>
                                                    <td data-label="Actions">
                                                        <div class="flex flex-col gap-2">
                                                            <button form="variety-update-{{ $variety->id }}" class="btn-secondary">Enregistrer</button>
                                                            <form method="POST" action="{{ route('backoffice.varieties.destroy', $variety) }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="btn-danger w-full" onclick="return confirm('Supprimer cette variété ?');">Supprimer</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @empty
                                        @endforelse
                                        @unless($hasVarieties)
                                            <tr><td colspan="5" class="px-4 py-6 text-center text-stone-500">Aucune variété référencée.</td></tr>
                                        @endunless
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="surface rounded-2xl">
                    <div class="surface-header"><h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Calibres et tares</h2></div>
                    <div class="surface-body space-y-6">
                        <div class="grid gap-6 lg:grid-cols-2">
                            <form method="POST" action="{{ route('backoffice.calibers.store') }}" class="space-y-3 border border-stone-200 p-4 lg:col-span-2">
                                @csrf
                                <div class="font-semibold text-stone-800">Ajouter un calibre</div>
                                <select name="fruit_id" class="input w-full" required>
                                    <option value="">Fruit</option>
                                    @foreach ($fruits as $fruit)
                                        <option value="{{ $fruit->id }}">{{ $fruit->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="name" placeholder="Nom du calibre" class="input w-full" required>
                                <input type="number" name="sort_order" min="1" value="1" class="input w-full" required>
                                <button class="btn-primary">Ajouter</button>
                            </form>
                            <form method="POST" action="{{ route('backoffice.tare-types.store') }}" class="space-y-3 border border-stone-200 p-4 lg:col-span-2">
                                @csrf
                                <div class="font-semibold text-stone-800">Ajouter une tare</div>
                                <input type="text" name="label" placeholder="Libellé de tare" class="input w-full" required>
                                <input type="number" name="weight_kg" step="0.001" min="0" value="0" class="input w-full" required>
                                <button class="btn-primary">Ajouter</button>
                            </form>
                        </div>
                        <div class="grid gap-6 text-sm text-stone-700">
                            <div class="overflow-x-auto border border-stone-200">
                                <table class="data-table tablet-stack">
                                    <thead>
                                        <tr>
                                            <th>Fruit</th>
                                            <th>Calibre</th>
                                            <th>Ordre</th>
                                            <th>Statut</th>
                                            <th>Usage</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-stone-100 bg-white">
                                        @forelse ($calibers as $caliber)
                                            <tr>
                                                <form id="caliber-update-{{ $caliber->id }}" method="POST" action="{{ route('backoffice.calibers.update', $caliber) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <td data-label="Fruit">
                                                        <select name="fruit_id" class="input w-full" required>
                                                            @foreach ($fruits as $fruit)
                                                                <option value="{{ $fruit->id }}" @selected($caliber->fruit_id === $fruit->id)>{{ $fruit->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td data-label="Calibre"><input type="text" name="name" value="{{ $caliber->name }}" class="input w-full" required></td>
                                                    <td data-label="Ordre"><input type="number" name="sort_order" min="1" value="{{ $caliber->sort_order }}" class="input w-full" required></td>
                                                    <td data-label="Statut">
                                                        <label class="inline-flex items-center gap-2 text-sm text-stone-700">
                                                            <input type="checkbox" name="is_active" value="1" class="rounded border-stone-300 text-[var(--castaneas-brown)] focus:ring-[var(--castaneas-brown)]" @checked($caliber->is_active)>
                                                            Actif
                                                        </label>
                                                    </td>
                                                    <td data-label="Usage" class="text-xs text-stone-500">{{ $caliber->calibrations_count }} calibrage(s)</td>
                                                </form>
                                                <td data-label="Actions">
                                                    <div class="flex flex-col gap-2">
                                                        <button form="caliber-update-{{ $caliber->id }}" class="btn-secondary">Enregistrer</button>
                                                        <form method="POST" action="{{ route('backoffice.calibers.destroy', $caliber) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn-danger w-full" onclick="return confirm('Supprimer ce calibre ?');">Supprimer</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="px-4 py-6 text-center text-stone-500">Aucun calibre référencé.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="overflow-x-auto border border-stone-200">
                                <table class="data-table tablet-stack">
                                    <thead>
                                        <tr>
                                            <th>Libellé</th>
                                            <th>Poids (kg)</th>
                                            <th>Statut</th>
                                            <th>Usage</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-stone-100 bg-white">
                                        @forelse ($tareTypes as $tareType)
                                            <tr>
                                                <form id="tare-update-{{ $tareType->id }}" method="POST" action="{{ route('backoffice.tare-types.update', $tareType) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <td data-label="Libellé"><input type="text" name="label" value="{{ $tareType->label }}" class="input w-full" required></td>
                                                    <td data-label="Poids (kg)"><input type="number" name="weight_kg" step="0.001" min="0" value="{{ number_format((float) $tareType->weight_kg, 3, '.', '') }}" class="input w-full" required></td>
                                                    <td data-label="Statut">
                                                        <label class="inline-flex items-center gap-2 text-sm text-stone-700">
                                                            <input type="checkbox" name="is_active" value="1" class="rounded border-stone-300 text-[var(--castaneas-brown)] focus:ring-[var(--castaneas-brown)]" @checked($tareType->is_active)>
                                                            Active
                                                        </label>
                                                    </td>
                                                    <td data-label="Usage" class="text-xs text-stone-500">{{ $tareType->calibrations_count }} calibrage(s)</td>
                                                </form>
                                                <td data-label="Actions">
                                                    <div class="flex flex-col gap-2">
                                                        <button form="tare-update-{{ $tareType->id }}" class="btn-secondary">Enregistrer</button>
                                                        <form method="POST" action="{{ route('backoffice.tare-types.destroy', $tareType) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn-danger w-full" onclick="return confirm('Supprimer cette tare ?');">Supprimer</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="px-4 py-6 text-center text-stone-500">Aucune tare référencée.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </article>
            </section>
        @elseif ($section === 'utilisateurs')
            <section class="grid gap-6 2xl:grid-cols-[420px_minmax(0,1fr)]">
                <article class="surface rounded-2xl">
                    <div class="surface-header"><h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Nouvel utilisateur</h2></div>
                    <div class="surface-body">
                        <form method="POST" action="{{ route('backoffice.users.store') }}" class="space-y-4">
                            @csrf
                            <input type="text" name="name" placeholder="Nom" class="input w-full" required>
                            <input type="email" name="email" placeholder="E-mail" class="input w-full" required>
                            <input type="password" name="password" placeholder="Mot de passe" class="input w-full" required>
                            <select name="role" class="input w-full" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <button class="btn-primary">Créer l'utilisateur</button>
                        </form>
                    </div>
                </article>

                <article class="surface overflow-hidden rounded-2xl">
                    <div class="surface-header"><h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Liste utilisateurs</h2></div>
                    <div class="overflow-x-auto">
                        <table class="data-table tablet-stack">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>E-mail</th>
                                    <th>Rôle</th>
                                    <th>Mot de passe</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 bg-white">
                                @foreach ($users as $user)
                                    <tr>
                                        <form id="user-update-{{ $user->id }}" method="POST" action="{{ route('backoffice.users.update', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <td data-label="Nom"><input type="text" name="name" value="{{ $user->name }}" class="input w-full" required></td>
                                            <td data-label="E-mail"><input type="email" name="email" value="{{ $user->email }}" class="input w-full" required></td>
                                            <td data-label="Rôle">
                                                <select name="role" class="input w-full" required>
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->name }}" @selected($user->roles->pluck('name')->contains($role->name))>{{ $role->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td data-label="Mot de passe"><input type="password" name="password" placeholder="Laisser vide pour conserver" class="input w-full"></td>
                                            <td data-label="Statut">
                                                <label class="inline-flex items-center gap-2 text-sm text-stone-700">
                                                    <input type="checkbox" name="is_active" value="1" class="rounded border-stone-300 text-[var(--castaneas-brown)] focus:ring-[var(--castaneas-brown)]" @checked($user->is_active)>
                                                    Actif
                                                </label>
                                            </td>
                                        </form>
                                        <td data-label="Actions">
                                            <div class="flex flex-col gap-2">
                                                <button form="user-update-{{ $user->id }}" class="btn-primary">Enregistrer</button>
                                                <form method="POST" action="{{ route('backoffice.users.destroy', $user) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn-danger w-full disabled:cursor-not-allowed disabled:opacity-50" onclick="return confirm('Supprimer cet utilisateur ?');" @disabled(auth()->id() === $user->id)>Supprimer</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        @else
            <section class="grid gap-6">
                <article class="surface rounded-2xl">
                    <div class="surface-header"><h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Filtres audit</h2></div>
                    <div class="surface-body">
                        <form method="GET" class="grid gap-3 md:grid-cols-3">
                            <input type="hidden" name="section" value="audit">
                            <input type="text" name="audit_event" value="{{ request('audit_event') }}" placeholder="Événement" class="input">
                            <select name="audit_user" class="input">
                                <option value="">Tous les utilisateurs</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected((string) request('audit_user') === (string) $user->id)>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            <button class="btn-primary">Filtrer</button>
                        </form>
                    </div>
                </article>
                <article class="surface overflow-hidden rounded-2xl">
                    <div class="surface-header"><h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Journal d'audit</h2></div>
                    <div class="overflow-x-auto">
                        <table class="data-table tablet-stack">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Événement</th>
                                    <th>Utilisateur</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 bg-white">
                                @forelse ($activities as $activity)
                                    <tr>
                                        <td data-label="Date">{{ $activity->created_at?->format('d/m/Y H:i') }}</td>
                                        <td data-label="Événement">{{ $activity->event ?? 'action' }}</td>
                                        <td data-label="Utilisateur">{{ $activity->causer?->name ?? 'Système' }}</td>
                                        <td data-label="Description">{{ $activity->description }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-6 text-center text-stone-500">Aucun log d'audit.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="surface-header">{{ $activities->links() }}</div>
                </article>
            </section>
        @endif
    </div>
</x-app-layout>