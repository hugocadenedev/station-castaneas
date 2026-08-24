<?php

namespace App\Http\Controllers;

use App\Models\Caliber;
use App\Models\Fruit;
use App\Models\Palox;
use App\Models\Reception;
use App\Models\Supplier;
use App\Models\Variety;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $paloxQuery = Palox::query()
            ->with(['reception.supplier', 'reception.fruit', 'reception.variety', 'calibration.caliber'])
            ->whereHas('reception', fn ($query) => $query->where('processing_status', 'calibrated'))
            ->latest('labeled_at');

        if ($request->filled('fruit_id')) {
            $paloxQuery->whereHas('reception', fn ($query) => $query->where('fruit_id', $request->integer('fruit_id')));
        }

        if ($request->filled('variety_id')) {
            $paloxQuery->whereHas('reception', fn ($query) => $query->where('variety_id', $request->integer('variety_id')));
        }

        if ($request->filled('supplier_id')) {
            $paloxQuery->whereHas('reception', fn ($query) => $query->where('supplier_id', $request->integer('supplier_id')));
        }

        if ($request->filled('availability_status')) {
            $paloxQuery->where('availability_status', $request->string('availability_status'));
        }

        if ($request->filled('caliber_id')) {
            $paloxQuery->whereHas('calibration', fn ($query) => $query->where('caliber_id', $request->integer('caliber_id')));
        }

        if ($request->filled('under_contract')) {
            $paloxQuery->where('under_contract', $request->string('under_contract')->value() === '1');
        }

        if ($request->filled('palox_number')) {
            $paloxQuery->where('palox_number', 'like', '%'.$request->string('palox_number')->value().'%');
        }

        if ($request->filled('net_weight_min')) {
            $paloxQuery->where('remaining_net_weight_kg', '>=', $request->float('net_weight_min'));
        }

        if ($request->filled('net_weight_max')) {
            $paloxQuery->where('remaining_net_weight_kg', '<=', $request->float('net_weight_max'));
        }

        $nonConformingQuery = Reception::query()
            ->with(['supplier', 'fruit', 'variety', 'operator'])
            ->where('conformity_status', 'non_conforming')
            ->latest('received_at');

        if ($request->filled('non_conforming_supplier_id')) {
            $nonConformingQuery->where('supplier_id', $request->integer('non_conforming_supplier_id'));
        }

        if ($request->filled('non_conforming_reception_number')) {
            $nonConformingQuery->where('reception_number', 'like', '%'.$request->string('non_conforming_reception_number')->value().'%');
        }

        return view('modules.stock.index', [
            'paloxes' => $paloxQuery->paginate(15, ['*'], 'palox_page')->withQueryString(),
            'nonConformingReceptions' => $nonConformingQuery->paginate(15, ['*'], 'non_conforming_page')->withQueryString(),
            'fruits' => Fruit::query()->where('is_active', true)->orderBy('name')->get(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('supplier_code')->get(),
            'varieties' => Variety::query()->where('is_active', true)->orderBy('name')->get(),
            'calibers' => Caliber::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function show(Palox $palox): View
    {
        return view('modules.stock.show', [
            'palox' => $palox->load([
                'reception.supplier',
                'reception.fruit',
                'reception.variety',
                'reception.operator',
                'calibration.caliber',
                'calibration.operator',
                'orders.operator',
            ]),
        ]);
    }
}