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
            box-sizing: border-box;
            overflow: hidden;
        }

        .palox-section {
            height: 21mm;
        }

        .ggn-section {
            height: 9mm;
            padding: 1.9mm 4.5mm 1.1mm;
            text-align: left;
        }

        .variety-section {
            height: 22mm;
        }

        .caliber-section {
            height: 18mm;
        }

        .weight-section {
            height: 18mm;
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
            font-size: 2.8mm;
            font-weight: 700;
            letter-spacing: 0.06em;
            line-height: 1;
            text-transform: uppercase;
        }

        .section-rail {
            position: relative;
            float: left;
            width: 16mm;
            height: 100%;
            padding: 0 1.4mm;
            border-right: 0.8mm solid #111;
            box-sizing: border-box;
        }

        .section-rail .section-label {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 16mm;
            text-align: center;
            white-space: nowrap;
            transform: translate(-50%, -50%) rotate(-90deg);
            transform-origin: center;
        }

        .section-content {
            margin-left: 16mm;
            height: 100%;
            padding: 0 3mm;
            box-sizing: border-box;
            text-align: center;
        }

        .palox-value {
            display: block;
            padding-top: 4.3mm;
            font-size: 11mm;
            font-weight: 700;
            line-height: 1;
            text-transform: uppercase;
        }

        .section-value {
            display: block;
            padding-top: 4.3mm;
            font-size: 11mm;
            font-weight: 700;
            line-height: 1;
            text-transform: uppercase;
            word-break: break-word;
        }

        .variety-value {
            padding-top: 3.4mm;
            font-size: 9.5mm;
            line-height: 1.02;
        }

        .caliber-value,
        .weight-value {
            font-size: 11mm;
            line-height: 1;
        }

        .section-clear {
            clear: both;
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
                <div class="section-rail">
                    <span class="section-label">N° Palox</span>
                </div>
                <div class="section-content">
                    <span class="palox-value">{{ $palox->palox_number }}</span>
                </div>
                <div class="section-clear"></div>
            </div>

            <div class="section variety-section">
                <div class="section-rail">
                    <span class="section-label">Variété</span>
                </div>
                <div class="section-content">
                    <span class="section-value variety-value">{{ $palox->reception->variety->name }}</span>
                </div>
                <div class="section-clear"></div>
            </div>

            <div class="section caliber-section">
                <div class="section-rail">
                    <span class="section-label">Calibre</span>
                </div>
                <div class="section-content">
                    <span class="section-value caliber-value">{{ $palox->calibration->caliber?->name ?? 'Sans calibre' }}</span>
                </div>
                <div class="section-clear"></div>
            </div>

            <div class="section weight-section">
                <div class="section-rail">
                    <span class="section-label">Poids</span>
                </div>
                <div class="section-content">
                    <span class="section-value weight-value">{{ rtrim(rtrim(number_format((float) $palox->remaining_net_weight_kg, 1, ',', ''), '0'), ',') }} kg</span>
                </div>
                <div class="section-clear"></div>
            </div>
        </div>
    </div>
</body>
</html>