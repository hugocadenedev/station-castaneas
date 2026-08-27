<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        html, body { margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; color: #111; }
        .page { width: 100mm; box-sizing: border-box; padding: 3mm; overflow: hidden; }
        .label { width: 94mm; border: 0.8mm solid #111; box-sizing: border-box; overflow: hidden; }
        .header { width: 100%; height: 18mm; overflow: hidden; }
        .header-fruit { float: left; width: 62mm; height: 18mm; padding: 4.7mm 4mm 1.2mm; box-sizing: border-box; overflow: hidden; }
        .header-supplier { float: left; width: 32mm; height: 18mm; padding: 2.5mm 3mm 1.2mm; box-sizing: border-box; border-left: 0.8mm solid #111; overflow: hidden; }
        .header-clear, .section-clear { clear: both; }
        .section { border-top: 0.8mm solid #111; box-sizing: border-box; overflow: hidden; }
        .reception-section { height: 21mm; }
        .date-section { height: 16mm; }
        .variety-section { height: 21mm; }
        .status-section { height: 17mm; }
        .fruit-value { font-size: 6mm; font-weight: 700; line-height: 1; text-transform: uppercase; word-break: break-word; }
        .meta-label { display: block; font-size: 2mm; font-weight: 700; line-height: 1.05; text-transform: uppercase; text-align: left; }
        .meta-value { display: block; margin-top: 1.5mm; font-size: 6mm; font-weight: 700; line-height: 1; text-align: left; }
        .section-label { display: block; font-size: 2.8mm; font-weight: 700; letter-spacing: 0.06em; line-height: 1; text-transform: uppercase; }
        .section-rail { position: relative; float: left; width: 16mm; height: 100%; padding: 0 1.4mm; border-right: 0.8mm solid #111; box-sizing: border-box; }
        .section-rail .section-label { position: absolute; top: 50%; left: 50%; width: 16mm; text-align: center; white-space: nowrap; transform: translate(-50%, -50%) rotate(-90deg); transform-origin: center; }
        .section-content { margin-left: 16mm; height: 100%; padding: 0 3mm; box-sizing: border-box; text-align: center; }
        .section-value { display: block; padding-top: 6.3mm; font-size: 6mm; font-weight: 700; line-height: 1; text-transform: uppercase; word-break: break-word; }
        .reception-value { white-space: nowrap; word-break: normal; }
        .date-value { padding-top: 4.8mm; }
        .variety-value { padding-top: 4.2mm; line-height: 1.02; }
        .status-value { padding-top: 5.2mm; }
        .status-non-conforming { color: #a11d1d; }
    </style>
</head>
<body>
    <div class="page">
        <div class="label">
        <div class="header">
            <div class="header-fruit"><div class="fruit-value">{{ $reception->fruit->name }}</div></div>
            <div class="header-supplier">
                <span class="meta-label">Fournisseur</span>
                <span class="meta-value">{{ $reception->supplier->supplier_code }}</span>
            </div>
            <div class="header-clear"></div>
        </div>

        <div class="section reception-section">
            <div class="section-rail"><span class="section-label">N° réception</span></div>
            <div class="section-content"><span class="section-value reception-value">{{ $reception->reception_number }}</span></div>
            <div class="section-clear"></div>
        </div>

        <div class="section date-section">
            <div class="section-rail"><span class="section-label">Date</span></div>
            <div class="section-content"><span class="section-value date-value">{{ $reception->received_at->format('d/m/Y') }}</span></div>
            <div class="section-clear"></div>
        </div>

        <div class="section variety-section">
            <div class="section-rail"><span class="section-label">Variété</span></div>
            <div class="section-content"><span class="section-value variety-value">{{ $reception->variety->name }}</span></div>
            <div class="section-clear"></div>
        </div>

        <div class="section status-section">
            <div class="section-rail"><span class="section-label">Conformité</span></div>
            <div class="section-content"><span class="section-value status-value {{ $reception->isNonConforming() ? 'status-non-conforming' : '' }}">{{ $reception->isNonConforming() ? 'Non conforme' : 'Conforme' }}</span></div>
            <div class="section-clear"></div>
        </div>
        </div>
    </div>
</body>
</html>