<?php

namespace App\Http\Controllers;

use App\Models\Caliber;
use App\Models\Customer;
use App\Models\Fruit;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Variety;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Illuminate\View\View;

class BackofficeController extends Controller
{
    public function index(Request $request): View
    {
        $activityQuery = Activity::query()->with('causer')->latest();

        if ($request->filled('audit_event')) {
            $activityQuery->where('event', $request->string('audit_event'));
        }

        if ($request->filled('audit_user')) {
            $activityQuery->where('causer_id', $request->integer('audit_user'));
        }

        return view('modules.backoffice.index', [
            'customers' => Customer::query()->orderBy('name')->get(),
            'fruits' => Fruit::query()->with('varieties')->orderBy('name')->get(),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'calibers' => Caliber::query()->with('fruit')->orderBy('sort_order')->get(),
            'users' => User::query()->with('roles')->orderBy('name')->get(),
            'roles' => Role::query()->orderBy('name')->get(),
            'activities' => $activityQuery->paginate(20)->withQueryString(),
        ]);
    }

    public function storeCustomer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'reference_code' => ['nullable', 'string', 'max:255', 'unique:customers,reference_code'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ], [], [
            'reference_code' => 'code GGN client',
        ]);

        Customer::query()->create($validated + ['is_active' => true]);

        return redirect()->route('backoffice.index', ['section' => 'clients'])->with('status', 'Client ajoute.');
    }

    public function storeFruit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:fruits,name'],
        ]);

        Fruit::query()->create($validated + ['is_active' => true]);

        return redirect()->route('backoffice.index', ['section' => 'production'])->with('status', 'Fruit ajoute.');
    }

    public function storeVariety(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fruit_id' => ['required', 'exists:fruits,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        Variety::query()->create($validated + ['is_active' => true]);

        return redirect()->route('backoffice.index', ['section' => 'production'])->with('status', 'Variete ajoutee.');
    }

    public function storeSupplier(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'ggn_code' => ['required', 'string', 'max:255', 'unique:suppliers,ggn_code'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        Supplier::query()->create($validated + ['is_active' => true]);

        return redirect()->route('backoffice.index', ['section' => 'fournisseurs'])->with('status', 'Fournisseur ajoute.');
    }

    public function storeCaliber(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fruit_id' => ['required', 'exists:fruits,id'],
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:1'],
        ]);

        Caliber::query()->create($validated + ['is_active' => true]);

        return redirect()->route('backoffice.index', ['section' => 'production'])->with('status', 'Calibre ajoute.');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('backoffice.index', ['section' => 'utilisateurs'])->with('status', 'Utilisateur ajoute.');
    }
}