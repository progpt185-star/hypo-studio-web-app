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
    @php
        $params = is_string($cluster->params) ? json_decode($cluster->params, true) : ($cluster->params ?? []);
        $features = $params['features'] ?? ['recency','frequency','spending'];
    @endphp
    <table>
        <thead>
            <tr>
                <th>Cluster</th>
                <th>Label</th>
                <th>Jumlah</th>
                @foreach($features as $f)
                    <th>Avg {{ ucfirst($f) }}@if($f === 'recency') (hari)@elseif(in_array($f, ['spending'])) (Rp)@endif</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @for ($i = 1; $i <= $cluster->k_value; $i++)
                <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $statistics[$i]['label'] ?? '-' }}</td>
                    <td>{{ $statistics[$i]['count'] ?? 0 }}</td>
                    @foreach($features as $f)
                        @php
                            $key = "avg_{$f}";
                            $val = $statistics[$i][$key] ?? ($statistics[$i]['averages'][$f] ?? 0);
                        @endphp
                        <td>
                            @if(is_null($val))
                                -
                            @elseif($f === 'recency')
                                {{ number_format($val, 2) }}
                            @elseif(in_array($f, ['spending']))
                                Rp {{ number_format($val, 0, ',', '.') }}
                            @else
                                {{ is_numeric($val) ? number_format($val, 2) : $val }}
                            @endif
                        </td>
                    @endforeach
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
