<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }

        html, body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111;
        }

        .page {
            width: 100mm;
            box-sizing: border-box;
            padding: 3mm;
            overflow: hidden;
        }

        .label {
            width: 94mm;
            border: 0.8mm solid #111;
            box-sizing: border-box;
            overflow: hidden;
        }

        .header {
            width: 100%;
            height: 18mm;
            overflow: hidden;
        }

        .header-fruit {
            float: left;
            width: 74mm;
            height: 18mm;
            padding: 3mm 4mm 1.2mm;
            box-sizing: border-box;
            overflow: hidden;
        }

        .header-supplier {
            float: left;
            width: 20mm;
            height: 18mm;
            padding: 3.4mm 3mm 1.2mm;
            box-sizing: border-box;
            border-left: 0.8mm solid #111;
            overflow: hidden;
        }

        .header-clear {
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
            padding: 3.2mm 4.8mm 1.6mm;
        }

        .ggn-section {
            height: 9mm;
            padding: 1.9mm 4.5mm 1.1mm;
            text-align: left;
        }

        .variety-section {
            height: 24mm;
            padding: 3.8mm 4.8mm 1.8mm;
        }

        .caliber-section {
            height: 16mm;
            padding: 3.2mm 4.8mm 1.4mm;
        }

        .weight-section {
            padding: 3.4mm 4.8mm 1.8mm;
        }

        .fruit-value {
            margin-top: 3.2mm;
            font-size: 6mm;
            font-weight: 700;
            line-height: 1;
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
            margin-top: 3.1mm;
            font-size: 3.6mm;
        }

        .ggn-value {
            display: block;
            margin-top: 1.1mm;
            font-size: 3mm;
            font-weight: 700;
            line-height: 1.05;
            letter-spacing: 0.04em;
            text-transform: uppercase;
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
            margin-top: 1.8mm;
            font-size: 11mm;
            font-weight: 700;
            line-height: 1;
            text-transform: uppercase;
        }

        .section-value {
            display: block;
            margin-top: 2mm;
            font-size: 7.6mm;
            font-weight: 700;
            line-height: 1;
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
                <div class="header-fruit">
                    <div class="fruit-value">{{ $palox->reception->fruit->name }}</div>
                </div>
                <div class="header-supplier">
                    <span class="meta-label">Code fournisseur</span>
                    <span class="meta-value supplier-code-value">{{ $palox->reception->supplier->supplier_code }}</span>
                </div>
                <div class="header-clear"></div>
            </div>

            <div class="section ggn-section">
                <span class="meta-label">GGN</span>
                <span class="ggn-value">{{ $palox->reception->supplier->ggn_code ?: '-' }}</span>
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