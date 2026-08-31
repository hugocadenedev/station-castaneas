<?php

namespace App\Http\Controllers;

use App\Models\Caliber;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\Fruit;
use App\Models\Palox;
use App\Models\Supplier;
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

        if ($request->filled('order_number')) {
            $query->where('order_number', 'like', '%'.$request->string('order_number')->value().'%');
        }

        return view('modules.commandes.index', [
            'orders' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('modules.commandes.create', [
            'fruits' => Fruit::query()->where('is_active', true)->orderBy('name')->get(),
            'varieties' => Variety::query()
                ->where('is_active', true)
                ->when($request->filled('fruit_id'), fn ($query) => $query->where('fruit_id', $request->integer('fruit_id')))
                ->orderBy('name')
                ->get(),
            'calibers' => Caliber::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('supplier_code')->get(),
            'availablePaloxes' => $this->availablePaloxes(
                $request->integer('fruit_id'),
                $request->integer('variety_id'),
                $request->integer('caliber_id'),
                $request->integer('supplier_id'),
            ),
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
            'client_name' => ['nullable', 'string', 'max:255'],
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
                    'client_name' => $customer?->name ?? ($validated['client_name'] ?: 'Client non renseigné'),
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

    private function availablePaloxes(?int $fruitId, ?int $varietyId, ?int $caliberId, ?int $supplierId = null)
    {
        return Palox::query()
            ->with(['reception.fruit', 'reception.variety', 'reception.supplier', 'calibration.caliber'])
            ->whereIn('availability_status', ['available', 'partial'])
            ->whereHas('reception', fn ($query) => $query->where('processing_status', 'calibrated'))
            ->when($fruitId, fn ($query) => $query->whereHas('reception', fn ($subQuery) => $subQuery->where('fruit_id', $fruitId)))
            ->when($varietyId, fn ($query) => $query->whereHas('reception', fn ($subQuery) => $subQuery->where('variety_id', $varietyId)))
            ->when($caliberId, fn ($query) => $query->whereHas('calibration', fn ($subQuery) => $subQuery->where('caliber_id', $caliberId)))
            ->when($supplierId, fn ($query) => $query->whereHas('reception', fn ($subQuery) => $subQuery->where('supplier_id', $supplierId)))
            ->orderBy('palox_number')
            ->get();
    }
}