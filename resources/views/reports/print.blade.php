<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rapor Çıktısı</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #0f172a; }
        h1 { margin: 0 0 6px; }
        p { margin: 0 0 14px; color: #334155; }
        .grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 16px; }
        .card { border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; }
        .muted { font-size: 12px; color: #64748b; }
        .value { font-size: 24px; font-weight: 700; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; font-size: 13px; text-align: left; }
        th { background: #f1f5f9; }
        @media print {
            .no-print { display: none; }
            body { margin: 10mm; }
        }
    </style>
</head>
<body>
<div class="no-print" style="margin-bottom: 12px;">
    <button onclick="window.print()">Yazdır / PDF Olarak Kaydet</button>
</div>

<h1>Rapor Çıktısı</h1>
<p>{{ $startDate->format('d.m.Y') }} - {{ $endDate->format('d.m.Y') }}</p>

<div class="grid">
    <div class="card">
        <div class="muted">Toplam Bakım Kaydı</div>
        <div class="value">{{ number_format($summary['total_records'], 0, ',', '.') }}</div>
    </div>
    <div class="card">
        <div class="muted">Toplam Ciro</div>
        <div class="value">₺{{ number_format($summary['total_revenue'], 2, ',', '.') }}</div>
    </div>
    <div class="card">
        <div class="muted">Ortalama Fiş Tutarı</div>
        <div class="value">₺{{ number_format($summary['avg_ticket'], 2, ',', '.') }}</div>
    </div>
</div>

<h3>Aylık Bakım / Ciro</h3>
<table>
    <thead>
    <tr>
        <th>Ay</th>
        <th>Kayıt</th>
        <th>Ciro</th>
    </tr>
    </thead>
    <tbody>
    @foreach($monthly as $row)
        <tr>
            <td>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $row->period)->translatedFormat('F Y') }}</td>
            <td>{{ $row->total_records }}</td>
            <td>₺{{ number_format((float) $row->total_revenue, 2, ',', '.') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h3>En Çok İşlem Gören Markalar</h3>
<table>
    <thead>
    <tr>
        <th>Marka</th>
        <th>Kayıt</th>
        <th>Ciro</th>
    </tr>
    </thead>
    <tbody>
    @foreach($topBrands as $brand)
        <tr>
            <td>{{ $brand->brand ?: 'Belirtilmedi' }}</td>
            <td>{{ $brand->total_records }}</td>
            <td>₺{{ number_format((float) $brand->total_revenue, 2, ',', '.') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

@if($isAdmin)
    <h3>En Aktif Ustalar</h3>
    <table>
        <thead>
        <tr>
            <th>Usta</th>
            <th>Kayıt</th>
            <th>Ciro</th>
        </tr>
        </thead>
        <tbody>
        @foreach($topMasters as $master)
            <tr>
                <td>{{ $master->name }}</td>
                <td>{{ $master->total_records }}</td>
                <td>₺{{ number_format((float) $master->total_revenue, 2, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
</body>
</html>
