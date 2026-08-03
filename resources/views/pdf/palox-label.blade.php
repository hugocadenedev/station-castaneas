<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: 80mm 50mm; margin: 0; }
        html, body {
            width: 80mm;
            height: 50mm;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #2d1d17;
            font-size: 8px;
            line-height: 1.15;
        }
        .page {
            width: 74mm;
            padding: 3mm;
        }
        .label {
            width: 100%;
            border: 1px solid #6f4128;
            padding: 2.5mm 3mm;
        }
        .title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2mm;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        td {
            vertical-align: top;
            padding: 0 0 1mm;
            word-break: break-word;
        }
        .full {
            padding-bottom: 1.2mm;
        }
        .label-name {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="label">
            <div class="title">Palox Castaneas</div>
            <table>
                <tr>
                    <td colspan="2" class="full"><span class="label-name">N° palox :</span> {{ $palox->palox_number }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="full"><span class="label-name">Date :</span> {{ $palox->labeled_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="full"><span class="label-name">ID fournisseur :</span> {{ $palox->reception->supplier->supplier_code }}</td>
                </tr>
                <tr>
                    <td><span class="label-name">Variété :</span> {{ $palox->reception->variety->name }}</td>
                    <td><span class="label-name">Calibre :</span> {{ $palox->calibration->caliber->name }}</td>
                </tr>
                <tr>
                    <td colspan="2"><span class="label-name">Poids net :</span> {{ number_format((float) $palox->remaining_net_weight_kg, 3, ',', ' ') }} kg</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>