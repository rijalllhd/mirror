<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 8px; }
        h1 { margin: 0 0 5px; font-size: 17px; }
        p { margin: 0 0 12px; color: #475569; }
        table { border-collapse: collapse; width: 100%; }
        thead { display: table-header-group; }
        th { background: #e2e8f0; font-size: 7px; }
        th, td { border: 1px solid #cbd5e1; padding: 4px; text-align: center; }
        td:first-child { text-align: left; }
    </style>
</head>
<body>
    <h1>Riwayat Sensor SKARP</h1>
    <p>Periode: {{ $periodLabel }} | Diekspor: {{ now('Asia/Jakarta')->format('d-m-Y H:i') }} WIB | Rekap harian</p>
    <table>
        <thead>
            <tr>
                <th rowspan="2">Tanggal</th><th rowspan="2">Data</th>
                <th colspan="3">Suhu (C)</th><th colspan="3">Kelembapan (%)</th>
                <th colspan="3">Amonia (ppm)</th><th colspan="3">THI</th>
            </tr>
            <tr>
                @foreach(range(1, 4) as $metric)<th>Min</th><th>Rata2</th><th>Maks</th>@endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['date'] }}</td><td>{{ $row['dataPoints'] }}</td>
                    <td>{{ number_format($row['suhu']['min'], 1) }}</td><td>{{ number_format($row['suhu']['avg'], 1) }}</td><td>{{ number_format($row['suhu']['max'], 1) }}</td>
                    <td>{{ number_format($row['kelembapan']['min'], 1) }}</td><td>{{ number_format($row['kelembapan']['avg'], 1) }}</td><td>{{ number_format($row['kelembapan']['max'], 1) }}</td>
                    <td>{{ number_format($row['amonia_ppm']['min'], 3) }}</td><td>{{ number_format($row['amonia_ppm']['avg'], 3) }}</td><td>{{ number_format($row['amonia_ppm']['max'], 3) }}</td>
                    <td>{{ number_format($row['thi']['min'], 2) }}</td><td>{{ number_format($row['thi']['avg'], 2) }}</td><td>{{ number_format($row['thi']['max'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="14">Tidak ada data pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
