<?php

namespace App\Http\Controllers;

use App\Models\Caliber;
use App\Models\Calibration;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\Fruit;
use App\Models\Palox;
use App\Models\Reception;
use App\Models\Supplier;
use App\Models\TareType;
use App\Models\User;
use App\Models\Variety;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Illuminate\View\View;

class BackofficeController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureDefaultRolesExist();

        $activityQuery = Activity::query()->with('causer')->latest();

        if ($request->filled('audit_event')) {
            $activityQuery->where('event', $request->string('audit_event'));
        }

        if ($request->filled('audit_user')) {
            $activityQuery->where('causer_id', $request->integer('audit_user'));
        }

        return view('modules.backoffice.index', [
            'customers' => Customer::query()->withCount('orders')->orderBy('name')->get(),
            'fruits' => Fruit::query()->with(['varieties', 'calibers'])->withCount(['receptions', 'varieties', 'calibers'])->orderBy('name')->get(),
            'suppliers' => Supplier::query()->withCount('receptions')->orderBy('supplier_code')->get(),
            'calibers' => Caliber::query()->with('fruit')->withCount('calibrations')->orderBy('sort_order')->get(),
            'tareTypes' => TareType::query()->withCount('calibrations')->orderBy('label')->get(),
            'users' => User::query()->with('roles')->orderBy('name')->get(),
            'roles' => Role::query()->orderBy('name')->get(),
            'activities' => $activityQuery->paginate(20)->withQueryString(),
        ]);
    }

    public function storeCustomer(Request $request): RedirectResponse
    {
        $validated = $this->validateCustomer($request);

        Customer::query()->create($validated + ['reference_code' => null, 'is_active' => true]);

        return redirect()->route('backoffice.index', ['section' => 'clients'])->with('status', 'Client ajoute.');
    }

    public function updateCustomer(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $this->validateCustomer($request, $customer);

        $customer->update($validated + ['reference_code' => null, 'is_active' => $request->boolean('is_active')]);

        return $this->redirectToSection('clients', 'Client mis a jour.');
    }

    public function destroyCustomer(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return $this->redirectToSection('clients', 'Client supprime.');
    }

    public function storeFruit(Request $request): RedirectResponse
    {
        $validated = $this->validateFruit($request);

        Fruit::query()->create($validated + ['is_active' => true]);

        return redirect()->route('backoffice.index', ['section' => 'production'])->with('status', 'Fruit ajoute.');
    }

    public function updateFruit(Request $request, Fruit $fruit): RedirectResponse
    {
        $validated = $this->validateFruit($request, $fruit);

        $fruit->update($validated + ['is_active' => $request->boolean('is_active')]);

        return $this->redirectToSection('production', 'Fruit mis a jour.');
    }

    public function destroyFruit(Fruit $fruit): RedirectResponse
    {
        $receptionsCount = $fruit->receptions()->count();

        if ($receptionsCount > 0) {
            return $this->redirectToSection('production', "Suppression impossible: ce fruit est lie a {$receptionsCount} reception(s).");
        }

        $calibrationsCount = Calibration::query()
            ->whereHas('caliber', fn ($query) => $query->where('fruit_id', $fruit->id))
            ->count();

        if ($calibrationsCount > 0) {
            return $this->redirectToSection('production', "Suppression impossible: ce fruit est lie a {$calibrationsCount} calibrage(s) via ses calibres.");
        }

        DB::transaction(function () use ($fruit): void {
            $fruit->varieties()->delete();
            $fruit->calibers()->delete();

            $fruit->delete();
        });

        return $this->redirectToSection('production', 'Fruit supprime.');
    }

    public function storeVariety(Request $request): RedirectResponse
    {
        $validated = $this->validateVariety($request);

        Variety::query()->create($validated + ['is_active' => true]);

        return redirect()->route('backoffice.index', ['section' => 'production'])->with('status', 'Variete ajoutee.');
    }

    public function updateVariety(Request $request, Variety $variety): RedirectResponse
    {
        $validated = $this->validateVariety($request, $variety);

        $variety->update($validated + ['is_active' => $request->boolean('is_active')]);

        return $this->redirectToSection('production', 'Variete mise a jour.');
    }

    public function destroyVariety(Variety $variety): RedirectResponse
    {
        if ($variety->receptions()->exists()) {
            return $this->redirectToSection('production', 'Suppression impossible: cette variete est deja utilisee en reception.');
        }

        $variety->delete();

        return $this->redirectToSection('production', 'Variete supprimee.');
    }

    public function storeSupplier(Request $request): RedirectResponse
    {
        $validated = $this->validateSupplier($request);

        Supplier::query()->create($validated + ['is_active' => true]);

        return redirect()->route('backoffice.index', ['section' => 'fournisseurs'])->with('status', 'Fournisseur ajoute.');
    }

    public function updateSupplier(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $this->validateSupplier($request, $supplier);

        $supplier->update($validated + ['is_active' => $request->boolean('is_active')]);

        return $this->redirectToSection('fournisseurs', 'Fournisseur mis a jour.');
    }

    public function destroySupplier(Supplier $supplier): RedirectResponse
    {
        $receptionsCount = $supplier->receptions()->count();

        if ($receptionsCount > 0) {
            return $this->redirectToSection('fournisseurs', "Suppression impossible: ce fournisseur est lie a {$receptionsCount} reception(s).");
        }

        $supplier->delete();

        return $this->redirectToSection('fournisseurs', 'Fournisseur supprime.');
    }

    public function storeCaliber(Request $request): RedirectResponse
    {
        $validated = $this->validateCaliber($request);

        Caliber::query()->create($validated + ['is_active' => true]);

        return redirect()->route('backoffice.index', ['section' => 'production'])->with('status', 'Calibre ajoute.');
    }

    public function updateCaliber(Request $request, Caliber $caliber): RedirectResponse
    {
        $validated = $this->validateCaliber($request, $caliber);

        $caliber->update($validated + ['is_active' => $request->boolean('is_active')]);

        return $this->redirectToSection('production', 'Calibre mis a jour.');
    }

    public function destroyCaliber(Caliber $caliber): RedirectResponse
    {
        if ($caliber->calibrations()->exists()) {
            return $this->redirectToSection('production', 'Suppression impossible: ce calibre est deja utilise en calibrage.');
        }

        $caliber->delete();

        return $this->redirectToSection('production', 'Calibre supprime.');
    }

    public function storeTareType(Request $request): RedirectResponse
    {
        $validated = $this->validateTareType($request);

        TareType::query()->create($validated + ['is_active' => true]);

        return $this->redirectToSection('production', 'Tare ajoutee.');
    }

    public function updateTareType(Request $request, TareType $tareType): RedirectResponse
    {
        $validated = $this->validateTareType($request, $tareType);

        $tareType->update($validated + ['is_active' => $request->boolean('is_active')]);

        return $this->redirectToSection('production', 'Tare mise a jour.');
    }

    public function destroyTareType(TareType $tareType): RedirectResponse
    {
        if ($tareType->calibrations()->exists()) {
            return $this->redirectToSection('production', 'Suppression impossible: cette tare est deja utilisee en calibrage.');
        }

        $tareType->delete();

        return $this->redirectToSection('production', 'Tare supprimee.');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $this->ensureDefaultRolesExist();

        $validated = $this->validateUser($request, true);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('backoffice.index', ['section' => 'utilisateurs'])->with('status', 'Utilisateur ajoute.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $this->ensureDefaultRolesExist();

        $validated = $this->validateUser($request, false, $user);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $request->boolean('is_active'),
        ]);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->syncRoles([$validated['role']]);

        return $this->redirectToSection('utilisateurs', 'Utilisateur mis a jour.');
    }

    public function destroyUser(User $user): RedirectResponse
    {
        if ((int) $user->id === (int) auth()->id()) {
            return $this->redirectToSection('utilisateurs', 'Suppression impossible: vous ne pouvez pas supprimer votre propre compte.');
        }

        if (
            Reception::query()->where('received_by', $user->id)->exists()
            || Calibration::query()->where('performed_by', $user->id)->exists()
            || Palox::query()->where('created_by', $user->id)->exists()
            || CustomerOrder::query()->where('created_by', $user->id)->exists()
        ) {
            return $this->redirectToSection('utilisateurs', 'Suppression impossible: cet utilisateur est deja lie a des operations historiques.');
        }

        $user->delete();

        return $this->redirectToSection('utilisateurs', 'Utilisateur supprime.');
    }

    private function redirectToSection(string $section, string $message): RedirectResponse
    {
        return redirect()->route('backoffice.index', ['section' => $section])->with('status', $message);
    }

    private function validateCustomer(Request $request, ?Customer $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function validateFruit(Request $request, ?Fruit $fruit = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('fruits', 'name')->ignore($fruit?->id)],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function validateVariety(Request $request, ?Variety $variety = null): array
    {
        return $request->validate([
            'fruit_id' => ['required', 'exists:fruits,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('varieties', 'name')
                    ->where(fn ($query) => $query->where('fruit_id', $request->integer('fruit_id')))
                    ->ignore($variety?->id),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function validateSupplier(Request $request, ?Supplier $supplier = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'supplier_code' => ['required', 'string', 'max:255', Rule::unique('suppliers', 'supplier_code')->ignore($supplier?->id)],
            'ggn_code' => ['required', 'string', 'max:255', Rule::unique('suppliers', 'ggn_code')->ignore($supplier?->id)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function validateCaliber(Request $request, ?Caliber $caliber = null): array
    {
        return $request->validate([
            'fruit_id' => ['required', 'exists:fruits,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('calibers', 'name')
                    ->where(fn ($query) => $query->where('fruit_id', $request->integer('fruit_id')))
                    ->ignore($caliber?->id),
            ],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function validateTareType(Request $request, ?TareType $tareType = null): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:255', Rule::unique('tare_types', 'label')->ignore($tareType?->id)],
            'weight_kg' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function validateUser(Request $request, bool $creating = true, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$creating ? 'required' : 'nullable', 'string', 'min:8'],
            'role' => ['required', Rule::exists('roles', 'name')->where(fn ($query) => $query->where('guard_name', 'web'))],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function ensureDefaultRolesExist(): void
    {
        Role::findOrCreate('superadmin', 'web');
        Role::findOrCreate('operateur', 'web');
    }
}