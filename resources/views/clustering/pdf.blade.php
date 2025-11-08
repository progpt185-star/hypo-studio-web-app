<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cluster Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f4f4f4; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Cluster - {{ $cluster->name }}</h2>
        <p>Tanggal Analisis: {{ optional($cluster->analysis_date)->format('d M Y H:i') ?? '-' }}</p>
    </div>

    <h4>Ringkasan per Cluster</h4>
    <table>
        <thead>
            <tr>
                <th>Cluster</th>
                <th>Label</th>
                <th>Jumlah</th>
                <th>Avg Recency (hari)</th>
                <th>Avg Frequency</th>
                <th>Avg Spending (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 1; $i <= $cluster->k_value; $i++)
                <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $statistics[$i]['label'] ?? '-' }}</td>
                    <td>{{ $statistics[$i]['count'] ?? 0 }}</td>
                    <td>{{ number_format($statistics[$i]['avg_recency'] ?? 0,2) }}</td>
                    <td>{{ number_format($statistics[$i]['avg_frequency'] ?? 0,2) }}</td>
                    <td>Rp {{ number_format($statistics[$i]['avg_spending'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <h4 style="margin-top:20px">Anggota (contoh 50 pertama)</h4>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Cluster</th>
                <th>Frequency</th>
                <th>Total Spent</th>
            </tr>
        </thead>
        <tbody>
            @php $c=0; @endphp
            @foreach ($cluster->clusterMembers as $m)
                @php if ($c++ >= 50) break; @endphp
                <tr>
                    <td>{{ $c }}</td>
                    <td>{{ $m->customer->name ?? '-' }}</td>
                    <td>{{ $m->cluster_number }}</td>
                    <td>{{ $m->frequency }}</td>
                    <td>Rp {{ number_format($m->total_spent,0,',','.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
