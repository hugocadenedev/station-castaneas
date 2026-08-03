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
                    <div class="border border-stone-200 bg-stone-50 p-4"><div class="text-xs uppercase tracking-[0.18em] text-stone-500">Références production</div><div class="mt-2 text-2xl font-semibold text-stone-900">{{ $fruits->count() + $fruits->sum(fn ($fruit) => $fruit->varieties->count()) + $calibers->count() }}</div></div>
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
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 bg-white">
                                @forelse ($customers as $customer)
                                    <tr>
                                        <td data-label="Client" class="font-semibold text-stone-800">{{ $customer->name }}</td>
                                        <td data-label="Code GGN">{{ $customer->reference_code ?: 'Non renseigné' }}</td>
                                        <td data-label="Contact">{{ $customer->contact_name ?: 'Non renseigné' }}</td>
                                        <td data-label="Coordonnées">{{ $customer->email ?: 'Sans e-mail' }}<div class="text-xs text-stone-500">{{ $customer->phone ?: 'Sans téléphone' }}</div></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-6 text-center text-stone-500">Aucun client référencé.</td></tr>
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
                                    <th>E-mail</th>
                                    <th>Téléphone</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 bg-white">
                                @forelse ($suppliers as $supplier)
                                    <tr>
                                        <td data-label="Fournisseur" class="font-semibold text-stone-800">{{ $supplier->name }}</td>
                                        <td data-label="GGN">{{ $supplier->ggn_code }}</td>
                                        <td data-label="E-mail">{{ $supplier->email ?: 'Non renseigné' }}</td>
                                        <td data-label="Téléphone">{{ $supplier->phone ?: 'Non renseigné' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-6 text-center text-stone-500">Aucun fournisseur enregistré.</td></tr>
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
                            <div class="font-semibold text-stone-800">Liste structurée</div>
                            <div class="mt-4 space-y-4 text-sm text-stone-700">
                                @foreach ($fruits as $fruit)
                                    <div>
                                        <div class="font-semibold text-stone-900">{{ $fruit->name }}</div>
                                        <div class="text-stone-600">{{ $fruit->varieties->pluck('name')->join(', ') ?: 'Aucune variété' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </article>

                <article class="surface rounded-2xl">
                    <div class="surface-header"><h2 class="font-display text-2xl text-[var(--castaneas-ink)]">Calibres</h2></div>
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
                        </div>
                        <div class="grid gap-6 text-sm text-stone-700">
                            <div class="border border-stone-200 p-4"><div class="font-semibold text-stone-800">Calibres actifs</div><div class="mt-3 space-y-2">@foreach ($calibers as $caliber)<div>{{ $caliber->fruit?->name }} - {{ $caliber->name }}</div>@endforeach</div></div>
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
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 bg-white">
                                @foreach ($users as $user)
                                    <tr>
                                        <td data-label="Nom" class="font-semibold text-stone-800">{{ $user->name }}</td>
                                        <td data-label="E-mail">{{ $user->email }}</td>
                                        <td data-label="Rôle">{{ $user->roles->pluck('name')->join(', ') ?: 'Sans rôle' }}</td>
                                        <td data-label="Statut">{{ $user->is_active ? 'Actif' : 'Inactif' }}</td>
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