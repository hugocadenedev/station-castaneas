<?php

namespace App\Http\Controllers;

use App\Models\Calibration;
use App\Models\Caliber;
use App\Models\Palox;
use App\Models\Reception;
use App\Models\TareType;
use App\Services\ReferenceNumberService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CalibrationController extends Controller
{
    public function __construct(private readonly ReferenceNumberService $referenceNumberService)
    {
    }

    public function index(Request $request): View
    {
        $query = Calibration::query()
            ->with(['reception.supplier', 'reception.variety', 'caliber', 'operator', 'palox'])
            ->latest('calibrated_at');

        if ($request->filled('reception_number')) {
            $query->whereHas('reception', fn ($subQuery) => $subQuery->where('reception_number', 'like', '%'.$request->string('reception_number')->value().'%'));
        }

        if ($request->filled('caliber_id')) {
            $query->where('caliber_id', $request->integer('caliber_id'));
        }

        return view('modules.calibrages.index', [
            'calibrations' => $query->paginate(15)->withQueryString(),
            'calibers' => Caliber::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('modules.calibrages.create', [
            'calibers' => Caliber::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'pendingReceptions' => Reception::query()
                ->with(['supplier', 'fruit', 'variety'])
                ->where('conformity_status', 'conforming')
                ->where('processing_status', 'pending')
                ->orderBy('received_at')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reception_id' => ['required', 'exists:receptions,id'],
            'caliber_id' => ['required', 'exists:calibers,id'],
            'tare_weight_kg' => ['required', 'numeric', 'min:0'],
            'calibrated_at' => ['required', 'date'],
            'net_weight_kg' => ['required', 'numeric', 'gt:0'],
            'waste_weight_kg' => ['required', 'numeric', 'min:0'],
            'under_contract' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $reception = Reception::query()->lockForUpdate()->findOrFail($validated['reception_id']);

            if ($reception->isNonConforming() || $reception->processing_status !== 'pending') {
                abort(422, 'Cette reception ne peut pas etre calibree.');
            }

            $calibration = Calibration::query()->create([
                ...$validated,
                'tare_type_id' => $this->manualTareTypeId(),
                'performed_by' => $request->user()->id,
            ]);

            $palox = Palox::query()->create([
                'reception_id' => $reception->id,
                'calibration_id' => $calibration->id,
                'created_by' => $request->user()->id,
                'palox_number' => 'TMP-PAL-'.Str::upper(Str::random(10)),
                'initial_net_weight_kg' => $validated['net_weight_kg'],
                'remaining_net_weight_kg' => $validated['net_weight_kg'],
                'under_contract' => (bool) ($validated['under_contract'] ?? false),
                'availability_status' => 'available',
                'labeled_at' => $validated['calibrated_at'],
            ]);

            $this->referenceNumberService->assignPaloxNumber($palox);

            $reception->update([
                'processing_status' => 'calibrated',
            ]);

            activity()
                ->causedBy($request->user())
                ->performedOn($palox)
                ->event('calibration_completed')
                ->log('Creation d\'un palox via calibrage');
        });

        return redirect()->route('calibrages.index')->with('status', 'Calibrage et creation du palox enregistres.');
    }

    private function manualTareTypeId(): int
    {
        return (int) TareType::query()->firstOrCreate(
            ['label' => 'Saisie manuelle'],
            ['weight_kg' => 0, 'is_active' => false],
        )->id;
    }

    public function label(Palox $palox)
    {
        activity()
            ->causedBy(request()->user())
            ->performedOn($palox)
            ->event('palox_label_printed')
            ->log('Impression etiquette palox');

        return Pdf::loadView('pdf.palox-label', [
            'palox' => $palox->load(['reception.supplier', 'reception.variety', 'calibration.caliber', 'creator']),
        ])->setPaper([0, 0, 226.77, 141.73])->stream($palox->palox_number.'.pdf');
    }
}