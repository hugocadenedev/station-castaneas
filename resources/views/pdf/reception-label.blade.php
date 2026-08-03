<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #2d1d17; font-size: 11px; margin: 10px; }
        .label { border: 2px solid #6f4128; border-radius: 10px; padding: 12px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 8px; }
        .row { margin-bottom: 4px; }
        .alert { margin-top: 8px; padding: 6px; background: #fce7e7; border: 1px solid #b44f32; color: #7d2f2f; font-weight: bold; }
    </style>
</head>
<body>
    <div class="label">
        <div class="title">Réception Castaneas</div>
        <div class="row"><strong>Date :</strong> {{ $reception->received_at->format('d/m/Y H:i') }}</div>
        <div class="row"><strong>N° réception :</strong> {{ $reception->reception_number }}</div>
        <div class="row"><strong>ID fournisseur :</strong> {{ $reception->supplier->supplier_code }}</div>
        <div class="row"><strong>Fruit :</strong> {{ $reception->fruit->name }}</div>
        <div class="row"><strong>Variété :</strong> {{ $reception->variety->name }}</div>
        <div class="row"><strong>Poids brut :</strong> {{ number_format((float) $reception->gross_weight_kg, 3, ',', ' ') }} kg</div>
        @if ($reception->isNonConforming())
            <div class="alert">NON CONFORME - {{ $reception->non_conformity_reason }}</div>
        @endif
    </div>
</body>
</html>