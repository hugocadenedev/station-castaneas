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
            'varieties' => Variety::query()->where('is_active', true)->orderBy('name')->get(),
            'calibers' => Caliber::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('supplier_code')->get(),
            'availablePaloxes' => $this->availablePaloxes(null, null, null, null),
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
                $customerId = $validated['customer_id'] ?? null;
                $clientName = $validated['client_name'] ?? null;
                $orderNumber = $validated['order_number'] ?? null;

                $customer = ! empty($customerId)
                    ? Customer::query()->find($customerId)
                    : null;

                $order = CustomerOrder::query()->create([
                    'customer_id' => $customer?->id,
                    'client_name' => $customer?->name ?? ($clientName ?: 'Client non renseigné'),
                    'order_number' => $orderNumber ?: 'TMP-CMD-'.Str::upper(Str::random(10)),
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

    public function edit(Request $request, CustomerOrder $commande): View
    {
        return view('modules.commandes.edit', [
            'order' => $commande->load([
                'customer',
                'paloxes.reception.supplier',
                'paloxes.reception.fruit',
                'paloxes.reception.variety',
                'paloxes.calibration.caliber',
            ]),
            'fruits' => Fruit::query()->where('is_active', true)->orderBy('name')->get(),
            'varieties' => Variety::query()->where('is_active', true)->orderBy('name')->get(),
            'calibers' => Caliber::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('supplier_code')->get(),
            'availablePaloxes' => $this->availablePaloxes(null, null, null, null, $commande),
        ]);
    }

    public function update(Request $request, CustomerOrder $commande): RedirectResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:255', 'unique:customer_orders,order_number,'.$commande->id],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.palox_id' => ['required', 'distinct', 'exists:paloxes,id'],
            'lines.*.picked_net_weight_kg' => ['nullable', 'numeric', 'gt:0'],
        ]);

        try {
            DB::transaction(function () use ($request, $validated, $commande) {
                $commande->update([
                    'order_number' => $validated['order_number'],
                ]);

                $this->stockService->updateOrderWithLines($commande, $validated['lines']);

                activity()
                    ->causedBy($request->user())
                    ->performedOn($commande)
                    ->event('order_updated')
                    ->log('Modification d\'une commande client');
            });
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['lines' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('commandes.index')->with('status', 'Commande mise a jour et stock recalcule.');
    }

    private function availablePaloxes(?int $fruitId, ?int $varietyId, ?int $caliberId, ?int $supplierId = null, ?CustomerOrder $order = null)
    {
        return Palox::query()
            ->with(['reception.fruit', 'reception.variety', 'reception.supplier', 'calibration.caliber'])
            ->where(function ($query) use ($order) {
                $query->whereIn('availability_status', ['available', 'partial']);

                if ($order) {
                    $query->orWhereHas('orders', fn ($subQuery) => $subQuery->whereKey($order->id));
                }
            })
            ->whereHas('reception', fn ($query) => $query->where('processing_status', 'calibrated'))
            ->when($fruitId, fn ($query) => $query->whereHas('reception', fn ($subQuery) => $subQuery->where('fruit_id', $fruitId)))
            ->when($varietyId, fn ($query) => $query->whereHas('reception', fn ($subQuery) => $subQuery->where('variety_id', $varietyId)))
            ->when($caliberId, fn ($query) => $query->whereHas('calibration', fn ($subQuery) => $subQuery->where('caliber_id', $caliberId)))
            ->when($supplierId, fn ($query) => $query->whereHas('reception', fn ($subQuery) => $subQuery->where('supplier_id', $supplierId)))
            ->orderBy('palox_number')
            ->get();
    }

}