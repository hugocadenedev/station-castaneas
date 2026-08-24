<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\Palox;
use App\Models\Variety;
use App\Services\ReferenceNumberService;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;

class CustomerOrderController extends Controller
{
    public function __construct(
        private readonly ReferenceNumberService $referenceNumberService,
        private readonly StockService $stockService,
    ) {
    }

    public function index(Request $request): View
    {
        $query = CustomerOrder::query()
            ->with(['customer', 'operator', 'paloxes.reception.supplier', 'paloxes.reception.fruit', 'paloxes.reception.variety', 'paloxes.calibration.caliber'])
            ->latest('ordered_at');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->integer('customer_id'));
        }

        if ($request->filled('client_name')) {
            $query->where('client_name', 'like', '%'.$request->string('client_name')->value().'%');
        }

        if ($request->filled('order_number')) {
            $query->where('order_number', 'like', '%'.$request->string('order_number')->value().'%');
        }

        return view('modules.commandes.index', [
            'orders' => $query->paginate(15)->withQueryString(),
            'customers' => Customer::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('modules.commandes.create', [
            'customers' => Customer::query()->where('is_active', true)->orderBy('name')->get(),
            'varieties' => Variety::query()->where('is_active', true)->orderBy('name')->get(),
            'availablePaloxes' => $this->availablePaloxes($request->integer('variety_id')),
        ]);
    }

    public function show(CustomerOrder $commande): View
    {
        return view('modules.commandes.show', [
            'order' => $commande->load([
                'customer',
                'operator',
                'paloxes.reception.supplier',
                'paloxes.reception.fruit',
                'paloxes.reception.variety',
                'paloxes.calibration.caliber',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'client_name' => ['nullable', 'required_without:customer_id', 'string', 'max:255'],
            'order_number' => ['nullable', 'string', 'max:255', 'unique:customer_orders,order_number'],
            'ordered_at' => ['required', 'date'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.palox_id' => ['required', 'distinct', 'exists:paloxes,id'],
            'lines.*.picked_net_weight_kg' => ['nullable', 'numeric', 'gt:0'],
        ]);

        try {
            DB::transaction(function () use ($request, $validated) {
                $customer = ! empty($validated['customer_id'])
                    ? Customer::query()->find($validated['customer_id'])
                    : null;

                $order = CustomerOrder::query()->create([
                    'customer_id' => $customer?->id,
                    'client_name' => $customer?->name ?? $validated['client_name'],
                    'order_number' => $validated['order_number'] ?: 'TMP-CMD-'.Str::upper(Str::random(10)),
                    'ordered_at' => $validated['ordered_at'],
                    'created_by' => $request->user()->id,
                ]);

                $this->referenceNumberService->assignOrderNumber($order);
                $this->stockService->createOrderWithLines($order, $validated['lines']);

                activity()
                    ->causedBy($request->user())
                    ->performedOn($order)
                    ->event('order_created')
                    ->log('Creation d\'une commande client');
            });
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['lines' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('commandes.index')->with('status', 'Commande enregistree et stock mis a jour.');
    }

    public function edit(CustomerOrder $commande): View
    {
        return view('modules.commandes.edit', [
            'order' => $commande,
        ]);
    }

    public function update(Request $request, CustomerOrder $commande): RedirectResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:255', 'unique:customer_orders,order_number,'.$commande->id],
        ]);

        $commande->update([
            'order_number' => $validated['order_number'],
        ]);

        activity()
            ->causedBy($request->user())
            ->performedOn($commande)
            ->event('order_number_updated')
            ->log('Modification du numero de commande');

        return redirect()->route('commandes.index')->with('status', 'Numero de commande mis a jour.');
    }

    private function availablePaloxes(?int $varietyId)
    {
        return Palox::query()
            ->with(['reception.fruit', 'reception.variety', 'reception.supplier', 'calibration.caliber'])
            ->whereIn('availability_status', ['available', 'partial'])
            ->whereHas('reception', fn ($query) => $query->where('processing_status', 'calibrated'))
            ->when($varietyId, fn ($query) => $query->whereHas('reception', fn ($subQuery) => $subQuery->where('variety_id', $varietyId)))
            ->orderBy('palox_number')
            ->get();
    }
}