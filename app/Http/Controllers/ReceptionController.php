<?php

namespace App\Http\Controllers;

use App\Models\Fruit;
use App\Models\Reception;
use App\Models\Supplier;
use App\Models\Variety;
use App\Services\ReferenceNumberService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReceptionController extends Controller
{
    public function __construct(private readonly ReferenceNumberService $referenceNumberService)
    {
    }

    public function index(Request $request): View
    {
        $query = Reception::query()
            ->with(['supplier', 'fruit', 'variety', 'operator'])
            ->latest('received_at');

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->integer('supplier_id'));
        }

        if ($request->filled('fruit_id')) {
            $query->where('fruit_id', $request->integer('fruit_id'));
        }

        if ($request->filled('variety_id')) {
            $query->where('variety_id', $request->integer('variety_id'));
        }

        if ($request->filled('conformity_status')) {
            $query->where('conformity_status', $request->string('conformity_status'));
        }

        if ($request->filled('reception_number')) {
            $query->where('reception_number', 'like', '%'.$request->string('reception_number')->value().'%');
        }

        return view('modules.receptions.index', [
            'receptions' => $query->paginate(15)->withQueryString(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('supplier_code')->get(),
            'fruits' => Fruit::query()->where('is_active', true)->orderBy('name')->get(),
            'varieties' => Variety::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('modules.receptions.create', $this->formData());
    }

    public function edit(Reception $reception): View
    {
        return view('modules.receptions.edit', [
            'reception' => $reception->load(['supplier', 'fruit', 'variety', 'operator']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateReception($request);

        $reception = Reception::query()->create([
            ...$validated,
            'reception_number' => 'TMP-REC-'.Str::upper(Str::random(10)),
            'received_by' => $request->user()->id,
            'processing_status' => $validated['conformity_status'] === 'non_conforming' ? 'stocked_non_conforming' : 'pending',
        ]);

        $this->referenceNumberService->assignReceptionNumber($reception);

        activity()
            ->causedBy($request->user())
            ->performedOn($reception)
            ->event('reception_created')
            ->log('Creation d\'une reception');

        return redirect()
            ->route('receptions.index')
            ->with('status', 'Reception enregistree avec succes.');
    }

    public function update(Request $request, Reception $reception): RedirectResponse
    {
        $validated = $request->validate([
            'gross_weight_kg' => ['nullable', 'numeric', 'gt:0'],
        ]);

        $reception->update($validated);

        activity()
            ->causedBy($request->user())
            ->performedOn($reception)
            ->event('reception_updated')
            ->log('Mise a jour du poids brut de la reception');

        return redirect()
            ->route('receptions.index')
            ->with('status', 'Reception mise a jour avec succes.');
    }

    public function label(Reception $reception)
    {
        activity()
            ->causedBy(request()->user())
            ->performedOn($reception)
            ->event('reception_label_printed')
            ->log('Impression etiquette reception');

        return Pdf::loadView('pdf.reception-label', [
            'reception' => $reception->load(['supplier', 'fruit', 'variety', 'operator']),
        ])->setPaper([0, 0, 226.77, 141.73])->stream($reception->reception_number.'.pdf');
    }

    public function destroy(Request $request, Reception $reception): RedirectResponse
    {
        $reception->load(['paloxes.orders', 'calibrations']);

        activity()
            ->causedBy($request->user())
            ->performedOn($reception)
            ->event('reception_deleted')
            ->log('Suppression d\'une reception');

        DB::transaction(function () use ($reception): void {
            foreach ($reception->paloxes as $palox) {
                $palox->orders()->detach();
                $palox->delete();
            }

            $reception->calibrations()->delete();
            $reception->delete();
        });

        return redirect()
            ->route('receptions.index')
            ->with('status', 'Reception supprimee avec succes.');
    }

    private function formData(): array
    {
        return [
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('supplier_code')->get(),
            'fruits' => Fruit::query()->where('is_active', true)->with(['varieties' => fn ($query) => $query->where('is_active', true)->orderBy('name')])->orderBy('name')->get(),
            'varietiesByFruit' => Variety::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->groupBy('fruit_id')
                ->map(fn ($varieties) => $varieties->map(fn (Variety $variety) => [
                    'id' => (string) $variety->id,
                    'name' => $variety->name,
                ])->values()->all())
                ->all(),
        ];
    }

    private function validateReception(Request $request): array
    {
        return $request->validate([
            'received_at' => ['required', 'date'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'fruit_id' => ['required', 'exists:fruits,id'],
            'variety_id' => [
                'required',
                Rule::exists('varieties', 'id')->where(fn ($query) => $query->where('fruit_id', $request->integer('fruit_id'))),
            ],
            'gross_weight_kg' => ['nullable', 'numeric', 'gt:0'],
            'conformity_status' => ['required', 'in:conforming,non_conforming'],
            'non_conformity_reason' => ['required_if:conformity_status,non_conforming', 'nullable', 'string', 'max:1000'],
        ]);
    }
}