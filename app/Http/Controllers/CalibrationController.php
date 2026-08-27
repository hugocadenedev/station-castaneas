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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CalibrationController extends Controller
{
    public function __construct(private readonly ReferenceNumberService $referenceNumberService)
    {
    }

    public function index(Request $request): View
    {
        $query = Reception::query()
            ->with(['supplier', 'fruit', 'variety', 'operator'])
            ->withCount('paloxes')
            ->withSum('calibrations', 'net_weight_kg')
            ->withSum('calibrations', 'waste_weight_kg')
            ->withMax('calibrations', 'calibrated_at')
            ->where('processing_status', 'calibrated')
            ->whereHas('calibrations');

        if ($request->filled('reception_number')) {
            $query->where('reception_number', 'like', '%'.$request->string('reception_number')->value().'%');
        }

        if ($request->filled('caliber_id')) {
            $query->whereHas('calibrations', fn ($subQuery) => $subQuery->where('caliber_id', $request->integer('caliber_id')));
        }

        return view('modules.calibrages.index', [
            'receptions' => $query->orderByDesc('calibrations_max_calibrated_at')->paginate(15)->withQueryString(),
            'calibers' => Caliber::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function show(Reception $reception): View
    {
        abort_unless($reception->processing_status === 'calibrated' && $reception->calibrations()->exists(), 404);

        return view('modules.calibrages.show', [
            'reception' => $reception->load([
                'supplier',
                'fruit',
                'variety',
                'operator',
                'paloxes.creator',
                'paloxes.orders',
                'calibrations.caliber',
                'calibrations.tareType',
                'calibrations.operator',
            ]),
        ]);
    }

    public function create(Request $request): View
    {
        $calibers = Caliber::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $pendingReceptions = Reception::query()
            ->with([
                'supplier',
                'fruit',
                'variety',
                'paloxes.calibration.caliber',
            ])
            ->where('conformity_status', 'conforming')
            ->where('processing_status', 'pending')
            ->orderBy('received_at')
            ->get();

        return view('modules.calibrages.create', [
            'calibersByFruit' => $calibers
                ->groupBy('fruit_id')
                ->map(fn ($group) => $group->map(fn (Caliber $caliber) => [
                    'id' => (string) $caliber->id,
                    'name' => $caliber->name,
                ])->values()->all())
                ->all(),
            'selectedReceptionId' => (string) $request->string('reception_id')->value(),
            'savedPaloxesByReception' => $pendingReceptions
                ->mapWithKeys(fn (Reception $reception) => [
                    (string) $reception->id => $reception->paloxes
                        ->sortBy('labeled_at')
                        ->values()
                        ->map(fn (Palox $palox) => [
                            'id' => (string) $palox->id,
                            'palox_number' => $palox->palox_number,
                            'label_url' => route('paloxes.label', $palox),
                            'caliber_name' => $palox->calibration?->caliber?->name ?? 'Calibre indisponible',
                            'net_weight_kg' => number_format((float) $palox->initial_net_weight_kg, 3, ',', ' '),
                            'under_contract' => $palox->under_contract,
                        ])
                        ->all(),
                ])
                ->all(),
            'tareTypes' => TareType::query()->where('is_active', true)->orderBy('label')->get(),
            'manualTareTypeId' => $this->manualTareTypeId(),
            'pendingReceptions' => $pendingReceptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reception_id' => ['required', 'exists:receptions,id'],
            'caliber_id' => ['nullable', 'exists:calibers,id'],
            'tare_type_id' => ['required', 'exists:tare_types,id'],
            'tare_weight_kg' => ['required', 'numeric', 'min:0'],
            'calibrated_at' => ['required', 'date'],
            'net_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'waste_weight_kg' => ['required', 'numeric', 'min:0'],
            'under_contract' => ['nullable', 'boolean'],
        ]);

        $validated['net_weight_kg'] = $validated['net_weight_kg'] ?? 0;

        if ((float) $validated['net_weight_kg'] === 0.0 && (float) $validated['waste_weight_kg'] === 0.0) {
            throw ValidationException::withMessages([
                'net_weight_kg' => 'Renseigne un poids net ou un poids dechet superieur a zero.',
            ]);
        }

        if ((float) $validated['net_weight_kg'] > 0 && empty($validated['caliber_id'])) {
            throw ValidationException::withMessages([
                'caliber_id' => 'Le calibre est obligatoire pour un poids net supérieur à zéro.',
            ]);
        }

        DB::transaction(function () use ($request, $validated) {
            $reception = Reception::query()->lockForUpdate()->findOrFail($validated['reception_id']);
            $caliber = ! empty($validated['caliber_id'])
                ? Caliber::query()->findOrFail($validated['caliber_id'])
                : null;

            if ($reception->isNonConforming() || $reception->processing_status !== 'pending') {
                abort(422, 'Cette reception ne peut pas etre calibree.');
            }

            if ($caliber && (int) $caliber->fruit_id !== (int) $reception->fruit_id) {
                throw ValidationException::withMessages([
                    'caliber_id' => 'Le calibre sélectionné ne correspond pas au fruit de la réception.',
                ]);
            }

            $calibration = Calibration::query()->create([
                ...$validated,
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

            activity()
                ->causedBy($request->user())
                ->performedOn($palox)
                ->event('palox_added_to_calibration')
                ->log('Ajout d\'un palox au calibrage');
        });

        return redirect()
            ->route('calibrages.create', ['reception_id' => $validated['reception_id']])
            ->with('status', 'Palox ajoute au calibrage. Tu peux imprimer son etiquette ou saisir le suivant.');
    }

    public function finalize(Request $request, Reception $reception): RedirectResponse
    {
        if ($reception->isNonConforming() || $reception->processing_status !== 'pending') {
            abort(422, 'Cette reception ne peut pas etre finalisee.');
        }

        if (! $reception->calibrations()->exists()) {
            throw ValidationException::withMessages([
                'reception_id' => 'Ajoute au moins un palox avant de valider le calibrage.',
            ]);
        }

        $reception->update([
            'processing_status' => 'calibrated',
        ]);

        activity()
            ->causedBy($request->user())
            ->performedOn($reception)
            ->event('calibration_finalized')
            ->log('Validation finale du calibrage');

        return redirect()->route('calibrages.index')->with('status', 'Calibrage valide avec succes.');
    }

    public function editPalox(Palox $palox): View
    {
        $palox->load(['reception.fruit', 'calibration.caliber', 'calibration.tareType']);

        return view('modules.calibrages.edit-palox', [
            'palox' => $palox,
            'calibers' => Caliber::query()
                ->where('fruit_id', $palox->reception->fruit_id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'tareTypes' => TareType::query()->where('is_active', true)->orderBy('label')->get(),
            'manualTareTypeId' => $this->manualTareTypeId(),
        ]);
    }

    public function updatePalox(Request $request, Palox $palox): RedirectResponse
    {
        $validated = $request->validate([
            'caliber_id' => ['nullable', 'exists:calibers,id'],
            'tare_type_id' => ['required', 'exists:tare_types,id'],
            'tare_weight_kg' => ['required', 'numeric', 'min:0'],
            'calibrated_at' => ['required', 'date'],
            'net_weight_kg' => ['required', 'numeric', 'min:0'],
            'waste_weight_kg' => ['required', 'numeric', 'min:0'],
            'under_contract' => ['nullable', 'boolean'],
        ]);

        if ((float) $validated['net_weight_kg'] > 0 && empty($validated['caliber_id'])) {
            throw ValidationException::withMessages([
                'caliber_id' => 'Le calibre est obligatoire pour un poids net supérieur à zéro.',
            ]);
        }

        DB::transaction(function () use ($request, $validated, $palox) {
            $palox->load(['calibration', 'orders']);
            $caliber = ! empty($validated['caliber_id']) ? Caliber::query()->findOrFail($validated['caliber_id']) : null;

            if ($caliber && (int) $caliber->fruit_id !== (int) $palox->reception->fruit_id) {
                throw ValidationException::withMessages([
                    'caliber_id' => 'Le calibre sélectionné ne correspond pas au fruit de la réception.',
                ]);
            }

            $pickedWeight = (float) $palox->orders->sum(fn ($order) => (float) $order->pivot->picked_net_weight_kg);
            $newNetWeight = (float) $validated['net_weight_kg'];

            if ($newNetWeight < $pickedWeight) {
                throw ValidationException::withMessages([
                    'net_weight_kg' => 'Le poids net ne peut pas être inférieur au poids déjà prélevé.',
                ]);
            }

            $calibration = $palox->calibration;
            $calibration->update([
                'caliber_id' => $validated['caliber_id'] ?? null,
                'tare_type_id' => $validated['tare_type_id'],
                'tare_weight_kg' => $validated['tare_weight_kg'],
                'calibrated_at' => $validated['calibrated_at'],
                'net_weight_kg' => $validated['net_weight_kg'],
                'waste_weight_kg' => $validated['waste_weight_kg'],
            ]);

            $remainingWeight = round($newNetWeight - $pickedWeight, 3);
            $palox->initial_net_weight_kg = $validated['net_weight_kg'];
            $palox->remaining_net_weight_kg = $remainingWeight;
            $palox->under_contract = (bool) ($validated['under_contract'] ?? false);
            $palox->availability_status = $remainingWeight === 0.0
                ? 'exhausted'
                : ($remainingWeight < $newNetWeight ? 'partial' : 'available');
            $palox->labeled_at = $validated['calibrated_at'];
            $palox->save();
        });

        return redirect()->route('calibrages.show', $palox->reception_id)->with('status', 'Palox modifié avec succès.');
    }

    public function destroyPalox(Palox $palox): RedirectResponse
    {
        $receptionId = $palox->reception_id;

        if ($palox->orders()->exists()) {
            throw ValidationException::withMessages([
                'palox' => 'Ce palox ne peut pas être supprimé car il est lié à une commande.',
            ]);
        }

        $hasRemainingCalibrations = DB::transaction(function () use ($palox, $receptionId) {
            $calibration = $palox->calibration;
            $palox->delete();
            $calibration?->delete();

            $reception = Reception::query()->findOrFail($receptionId);
            $hasRemainingCalibrations = $reception->calibrations()->exists();
            if (! $hasRemainingCalibrations) {
                $reception->update(['processing_status' => 'pending']);
            }

            return $hasRemainingCalibrations;
        });

        return redirect()
            ->route($hasRemainingCalibrations ? 'calibrages.show' : 'calibrages.create', $hasRemainingCalibrations ? ['reception' => $receptionId] : ['reception_id' => $receptionId])
            ->with('status', 'Palox supprimé avec succès.');
    }

    public function destroyLastPalox(Request $request, Reception $reception): RedirectResponse
    {
        if ($reception->isNonConforming() || $reception->processing_status !== 'pending') {
            abort(422, 'Cette reception ne peut pas etre modifiee.');
        }

        $deleted = DB::transaction(function () use ($request, $reception) {
            $palox = $reception->paloxes()
                ->with('calibration')
                ->latest('labeled_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $palox) {
                return false;
            }

            $calibration = $palox->calibration;
            $paloxNumber = $palox->palox_number;

            $palox->delete();
            $calibration?->delete();

            activity()
                ->causedBy($request->user())
                ->performedOn($reception)
                ->event('last_palox_removed_from_calibration')
                ->log('Retrait du dernier palox du calibrage: '.$paloxNumber);

            return true;
        });

        if (! $deleted) {
            throw ValidationException::withMessages([
                'reception_id' => 'Aucun palox a retirer pour cette reception.',
            ]);
        }

        return redirect()
            ->route('calibrages.create', ['reception_id' => $reception->id])
            ->with('status', 'Dernier palox retire du calibrage.');
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
            'palox' => $palox->load(['reception.supplier', 'reception.fruit', 'reception.variety', 'calibration.caliber', 'creator']),
        ])->setPaper([0, 0, 283.46, 425.20])->stream($palox->palox_number.'.pdf');
    }
}