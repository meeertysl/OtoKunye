<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Etiket Yazdır - {{ $vehicle->license_plate }}</title>
    <style>
        :root {
            color-scheme: only light;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: #000000;
            font-family: Arial, Helvetica, sans-serif;
        }

        .screen-actions {
            padding: 16px;
            text-align: center;
        }

        .back-btn {
            display: inline-block;
            text-decoration: none;
            color: #111827;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 14px;
            font-weight: 600;
        }

        .label-sheet {
            width: 50mm;
            height: 50mm;
            margin: 0 auto;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .label-content {
            width: 100%;
            height: 100%;
            padding: 2.5mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            text-align: center;
        }

        .brand {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .brand img {
            width: 15mm;
            height: 9mm;
            object-fit: contain;
        }

        .plate {
            width: 100%;
            border-radius: 2mm;
            padding: 1mm 1.2mm;
            background: #0f172a;
            color: #ffffff;
            font-size: 3.9mm;
            font-weight: 800;
            letter-spacing: 0.18mm;
            line-height: 1.1;
        }

        .qr {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .qr-frame {
            border: 0.45mm solid #0f172a;
            border-radius: 1.6mm;
            padding: 1.1mm;
            background: #ffffff;
        }

        .qr svg {
            width: 27.5mm;
            height: 27.5mm;
            display: block;
        }

        .hint {
            width: 100%;
            font-size: 2.45mm;
            font-weight: 600;
            letter-spacing: 0.08mm;
            color: #334155;
        }

        @page {
            size: 50mm 50mm;
            margin: 0;
        }

        @media print {
            html, body {
                width: 50mm;
                height: 50mm;
            }

            .screen-actions {
                display: none !important;
            }

            .label-sheet {
                margin: 0;
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>
<div class="screen-actions">
    <a class="back-btn" href="{{ route('vehicles.show', $vehicle) }}">Geri Dön</a>
</div>

<div class="label-sheet">
    <div class="label-content">
        <div class="brand">
            <img src="{{ asset('images/otokunye-logo.png') }}" alt="OTOKÜNYE">
        </div>
        <div class="plate">{{ $vehicle->license_plate }}</div>
        <div class="qr">
            <div class="qr-frame">
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(170)->margin(0)->generate(route('qr.scan', $vehicle->uuid)) !!}
            </div>
        </div>
        <div class="hint">Sisteme Okutunuz</div>
    </div>
</div>

<script>
    window.onload = function() { window.print(); };
</script>
</body>
</html>
