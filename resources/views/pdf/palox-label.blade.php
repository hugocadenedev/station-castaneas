<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: 100mm 150mm; margin: 0; }

        html, body {
            width: 100mm;
            height: 150mm;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111;
        }

        .page {
            width: 100mm;
            height: 150mm;
            padding: 3mm;
            box-sizing: border-box;
        }

        .label {
            width: 94mm;
            border: 0.8mm solid #111;
            box-sizing: border-box;
        }

        .header {
            width: 100%;
            overflow: hidden;
        }

        .header-col {
            float: left;
            height: 18mm;
            padding: 1.5mm 2mm;
            box-sizing: border-box;
            overflow: hidden;
        }

        .header-col + .header-col {
            border-left: 0.8mm solid #111;
        }

        .fruit-col {
            width: 58mm;
        }

        .supplier-col {
            width: 16mm;
        }

        .ggn-col {
            width: 20mm;
        }

        .clearfix {
            clear: both;
        }

        .section {
            border-top: 0.8mm solid #111;
            text-align: center;
            box-sizing: border-box;
            overflow: hidden;
        }

        .palox-section {
            height: 20mm;
            padding: 2.5mm 4mm 0;
        }

        .variety-section {
            height: 24mm;
            padding: 3mm 4mm 0;
        }

        .caliber-section {
            height: 16mm;
            padding: 3mm 4mm 0;
        }

        .weight-section {
            height: 18mm;
            padding: 3mm 4mm 0;
        }

        .fruit-value {
            margin-top: 5mm;
            font-size: 6mm;
            font-weight: 700;
            line-height: 0.95;
            text-transform: uppercase;
            word-break: break-word;
        }

        .meta-label {
            display: block;
            font-size: 2mm;
            font-weight: 700;
            line-height: 1.05;
            text-transform: uppercase;
            text-align: left;
        }

        .meta-value {
            display: block;
            font-weight: 700;
            text-align: left;
            line-height: 1.05;
        }

        .supplier-code-value {
            margin-top: 2.2mm;
            font-size: 3.6mm;
        }

        .ggn-value {
            margin-top: 0.8mm;
            font-size: 3mm;
            word-break: break-all;
        }

        .section-label {
            display: block;
            font-size: 3.5mm;
            font-weight: 700;
            letter-spacing: 0.06em;
            line-height: 1;
            text-transform: uppercase;
        }

        .palox-value {
            display: block;
            margin-top: 1.2mm;
            font-size: 11mm;
            font-weight: 700;
            line-height: 1;
            text-transform: uppercase;
        }

        .section-value {
            display: block;
            margin-top: 1.4mm;
            font-size: 7.6mm;
            font-weight: 700;
            line-height: 0.98;
            text-transform: uppercase;
            word-break: break-word;
        }

        .weight-value {
            font-size: 7.1mm;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="label">
            <div class="header">
                <div class="header-col fruit-col">
                    <div class="fruit-value">{{ $palox->reception->fruit->name }}</div>
                </div>
                <div class="header-col supplier-col">
                    <span class="meta-label">Code fournisseur</span>
                    <span class="meta-value supplier-code-value">{{ $palox->reception->supplier->supplier_code }}</span>
                </div>
                <div class="header-col ggn-col">
                    <span class="meta-label">GGN</span>
                    <span class="meta-value ggn-value">{{ $palox->reception->supplier->ggn_code ?: '-' }}</span>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="section palox-section">
                <span class="section-label">N° Palox</span>
                <span class="palox-value">{{ $palox->palox_number }}</span>
            </div>

            <div class="section variety-section">
                <span class="section-label">Variété</span>
                <span class="section-value">{{ $palox->reception->variety->name }}</span>
            </div>

            <div class="section caliber-section">
                <span class="section-label">Calibre</span>
                <span class="section-value">{{ $palox->calibration->caliber->name }}</span>
            </div>

            <div class="section weight-section">
                <span class="section-label">Poids</span>
                <span class="section-value weight-value">{{ number_format((float) $palox->remaining_net_weight_kg, 3, ',', ' ') }} kg</span>
            </div>
        </div>
    </div>
</body>
</html>