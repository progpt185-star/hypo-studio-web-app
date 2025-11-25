@extends('layouts.app')

@section('title', 'Hasil Segmentasi - Hypo Studio')

@section('css')
<style>
    .chart-section {
        transition: opacity 0.3s ease-in-out;
    }
    .chart-section.hidden {
        display: none;
        opacity: 0;
    }
    .chart-section.visible {
        display: block;
        opacity: 1;
    }
    .btn-group .btn-outline-primary.active {
        background-color: #0d6efd;
        color: white;
    }
    /* Compact inline legend for cluster scatter */
    #clusterLegend {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }
    #clusterLegend > div {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.86rem;
        color: #333;
        margin-left: 4px;
    }
    #clusterLegend > div span {
        display: inline-block;
        width: 18px;
        height: 12px;
        border-radius: 3px;
        border: 1px solid rgba(0,0,0,0.08);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-pie"></i> Hasil Segmentasi
            </h1>
        </div>
        <div class="col-md-6 text-end d-flex justify-content-end align-items-center">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-outline-primary" data-chart-toggle="statistics">
                    <i class="fas fa-chart-bar"></i> Statistik
                </button>
                <button type="button" class="btn btn-outline-primary" data-chart-toggle="distributions">
                    <i class="fas fa-chart-pie"></i> Distribusi
                </button>
                <button type="button" class="btn btn-outline-primary" data-chart-toggle="products">
                    <i class="fas fa-box"></i> Produk
                </button>
            </div>
            <a href="{{ url('/clustering') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12 text-end">
                <div class="btn-group me-2">
                    <button class="btn btn-outline-secondary" type="button" d{{--bs-toggle="collapse" data-bs-target="#debugCluster" aria-expanded="false" aria-controls="#debugCluster">
                        Tampilkan Data $cluster (debug)
                    </button>
                </div>

                <div class="collapse mt-2" id="debugCluster">
                    <div class="card card-body">
                        <strong>Cluster (array):</strong>
                        {{-- <pre class="small mb-0">{{ json_encode($cluster->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre> --}}
                    </div>
                </div>
                <form method="get" action="{{ url('/clustering/export/' . $cluster->id) }}" class="d-inline-block me-2">
                    <div class="input-group input-group-sm">
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDateStr ?? '' }}" />
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDateStr ?? '' }}" />
                        <button type="submit" name="format" value="xlsx" class="btn btn-success">Export XLSX</button>
                        <button type="submit" name="format" value="csv" class="btn btn-outline-secondary">Export CSV</button>
                        <button type="submit" name="format" value="pdf" class="btn btn-outline-dark">Download PDF</button>
                    </div>
                </form>

                <a href="{{ url('/clustering/rerun/' . $cluster->id) }}" class="btn btn-warning">Rerun Analisis (simpan params)</a>
            
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-info">
                <strong>Analisis:</strong> {{ $cluster->name }} | 
                <strong>Tanggal:</strong> {{ optional($cluster->analysis_date)->format('d M Y H:i') ?? 'N/A' }}
            </div>
        </div>
    </div>

    <!-- Statistics (per-feature per cluster) -->
    <div class="row mb-4">
        @php
            $params = is_string($cluster->params) ? json_decode($cluster->params, true) : ($cluster->params ?? []);
            $defaultFeatures = ['recency','frequency','spending'];
        @endphp
        @for ($i = 1; $i <= $cluster->k_value; $i++)
            @php
                $label = $statistics[$i]['label'] ?? null;
                $count = $statistics[$i]['count'] ?? 0;
                $features = $params['features'] ?? $defaultFeatures;
                $statsForI = $statistics[$i] ?? [];
            @endphp
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
                            Kelompok {{ $i }}
                            <span class="cluster-label" data-cluster="{{ $i }}">
                                @if($label) - {{ $label }} @endif
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2 edit-label-btn" 
                                    data-cluster="{{ $i }}" 
                                    data-current="{{ $label ?? '' }}">
                                <i class="fas fa-edit"></i>
                            </button>
                        </h5>
                        <hr>
                        <p class="mb-2"><strong>Jumlah Pelanggan:</strong><br>
                            <span class="badge bg-primary" style="font-size: 14px;">{{ $count }}</span>
                        </p>
                        @foreach($features as $f)
                            @php
                                $key = "avg_{$f}";
                                $val = $statsForI[$key] ?? ($statsForI['averages'][$f] ?? null);
                            @endphp
                            <p class="mb-2"><strong>Rata-rata {{ ucfirst($f) }}:</strong><br>
                                @if(is_null($val))
                                    -
                                @else
                                    @if(in_array($f, ['spending']) || Str::contains($f, 'spend'))
                                        Rp {{ number_format($val, 0, ',', '.') }}
                                    @elseif($f === 'recency')
                                        {{ number_format($val, 2) }} hari
                                    @elseif($f === 'frequency')
                                        {{ number_format($val, 2) }} transaksi
                                    @else
                                        {{ is_numeric($val) ? number_format($val, 2) : $val }}
                                    @endif
                                @endif
                            </p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endfor
    </div>

    <!-- Centroids -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Nilai Tengah Kelompok (rata-rata fitur per kelompok)</h5>
                </div>
                <div class="card-body">
                    @php
                        $centroids = $params['centroids'] ?? [];
                        $featuresList = $params['features'] ?? ['recency','frequency','spending'];
                    @endphp
                    @if(!empty($centroids))
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Kelompok</th>
                                        @foreach($featuresList as $f)
                                            <th>{{ ucfirst($f) }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($centroids as $idx => $cent)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            @foreach($cent as $val)
                                                <td>{{ is_numeric($val) ? number_format($val, 2) : $val }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <canvas id="centroidRadar" height="160"></canvas>
                        </div>
                    @else
                        <p class="text-muted">Centroid tidak tersedia.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Cluster Members Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-table"></i> Daftar Pelanggan per Kelompok
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Cluster</th>
                                    <th>Frekuensi</th>
                                    <th>Total Spending</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cluster->clusterMembers as $key => $member)
                                    @php
                                        $clusterNames = ['Pelanggan Loyal', 'Pelanggan Reguler', 'Pelanggan Sporadis', 'Pelanggan Baru', 'Pelanggan VIP'];
                                    @endphp
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <strong>{{ $member->customer->name ?? '-' }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">Kelompok {{ $member->cluster_number ?? '-' }}</span>
                                            @if(!empty($statistics[$member->cluster_number]['label']))
                                                <br><small>{{ $statistics[$member->cluster_number]['label'] }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $member->frequency }}</span>
                                        </td>
                                        <td>
                                            <strong>Rp {{ number_format($member->total_spent, 0, ',', '.') }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">
                                            Tidak ada data
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-bar-chart"></i> Distribusi Pelanggan per Kelompok
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="clusterDistribution" height="80"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Rata-rata Transaksi per Kelompok
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="spendingChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($timeSeries['labels']))
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-chart-area"></i> Time-series Pendapatan per Kelompok</h5>
                </div>
                <div class="card-body">
                    <canvas id="timeSeriesChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Scatter: Frequency vs Total Spending + Legend -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-chart-scatter"></i> Scatter: Frekuensi vs Total Spending</h5>
                    <div id="clusterLegend" class="d-flex gap-2 align-items-center"></div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <canvas id="scatterChart" height="160"></canvas>
                    </div>
                    <small class="text-muted">Setiap titik merepresentasikan 1 pelanggan. Warna menunjukkan kelompok.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Type Distribution -->
    <div class="row mt-4">
        @for ($i = 1; $i <= $cluster->k_value; $i++)
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie"></i> Distribusi Jenis Produk - Kelompok {{ $i }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="productTypeChart{{ $i }}" height="80"></canvas>
                    </div>
                </div>
            </div>
        @endfor
    </div>

    <!-- Actions -->
    <div class="row mt-4">
        <div class="col-md-12">
            <a href="{{ url('/clustering') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Analisis
            </a>
            <a href="{{ url('/clustering/history') }}" class="btn btn-info">
                <i class="fas fa-history"></i> Riwayat
            </a>
        </div>
    </div>
</div>
    <!-- Label Edit Modal -->
    <div class="modal fade" id="editLabelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Label Kelompok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editLabelForm">
                        <input type="hidden" name="cluster_number" id="editClusterNumber">
                        <div class="form-group">
                            <label for="editLabel">Label</label>
                            <select class="form-control" id="editLabel" name="label">
                                <option value="Loyal">Loyal</option>
                                <option value="Regular">Regular</option>
                                <option value="New">Baru</option>
                                <option value="Churn">Tidak Aktif</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="saveLabelBtn">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script
    @php
        // Guard k_value and prepare chart data to avoid division by zero
        $k = (int) ($cluster->k_value ?? 0);
        if ($k <= 0) {
            $k = 1; // fallback
        }

        // Build arrays from $statistics (passed from controller)
        $labels = [];
        $distributionCounts = [];
        $avgRecencyArr = [];
        $avgFreqArr = [];
        $avgSpendingArr = [];
        for ($i = 1; $i <= $k; $i++) {
            $labels[] = "Kelompok $i";
            $distributionCounts[] = (int) ($statistics[$i]['count'] ?? 0);
            $avgRecencyArr[] = (float) ($statistics[$i]['avg_recency'] ?? 0);
            $avgFreqArr[] = (float) ($statistics[$i]['avg_frequency'] ?? 0);
            $avgSpendingArr[] = (float) ($statistics[$i]['avg_spending'] ?? 0);
        }
    @endphp
    // Cluster Distribution Chart
    var distributionCtx = document.getElementById('clusterDistribution').getContext('2d');
    new Chart(distributionCtx, {
        type: 'bar',
        data: {
            labels: @json($labels),
            datasets: [{
                label: 'Jumlah Pelanggan',
                data: @json($distributionCounts),
                backgroundColor: 'rgba(102, 126, 234, 0.5)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Combined feature chart (multiple axes)
    var multiFeatureCtx = document.getElementById('spendingChart').getContext('2d');
    new Chart(multiFeatureCtx, {
        type: 'line',
        data: {
            labels: @json($labels),
            datasets: [
                {

                // Centroid Radar Chart
                (function(){
                    var centroids = @json($params['centroids'] ?? []);
                    var features = @json($featuresList ?? []);
                    // Centroid Radar Chart
                    if (centroids && centroids.length) {
                        var datasets = centroids.map(function(c, i){
                            return {
                                label: 'Kelompok ' + (i+1),
                                data: c,
                                fill: true,
                                backgroundColor: 'rgba(' + (50 + i*40 % 200) + ',' + (100 + i*30 % 155) + ',200,0.15)',
                                borderColor: 'rgba(' + (50 + i*40 % 200) + ',' + (100 + i*30 % 155) + ',200,1)'
                            };
                        });

                        var radarCtx = document.getElementById('centroidRadar').getContext('2d');
                        new Chart(radarCtx, {
                            type: 'radar',
                            data: { labels: features.map(function(f){ return f.charAt(0).toUpperCase()+f.slice(1); }), datasets: datasets },
                            options: { responsive: true, maintainAspectRatio: false }
                        });
                    }
                })();
                    label: 'Rata-rata ' + (features[0] ? (features[0].charAt(0).toUpperCase()+features[0].slice(1)) : 'Feature 1') + (features[0] === 'recency' ? ' (hari)' : (features[0] === 'spending' ? ' (Rp)' : '')),
                    data: @json($avgRecencyArr),
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    yAxisID: 'yRecency',
                    tension: 0.3,
                    fill: false,
                },
                {
                    label: 'Rata-rata ' + (features[1] ? (features[1].charAt(0).toUpperCase()+features[1].slice(1)) : 'Feature 2') + (features[1] === 'recency' ? ' (hari)' : (features[1] === 'spending' ? ' (Rp)' : '')),
                    data: @json($avgFreqArr),
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    yAxisID: 'yFreq',
                    tension: 0.3,
                    fill: false,
                },
                {
                    label: 'Rata-rata ' + (features[2] ? (features[2].charAt(0).toUpperCase()+features[2].slice(1)) : 'Feature 3') + (features[2] === 'recency' ? ' (hari)' : (features[2] === 'spending' ? ' (Rp)' : '')),
                    data: @json($avgSpendingArr),
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    yAxisID: 'yMoney',
                    tension: 0.3,
                    fill: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: { mode: 'index', intersect: false },
            stacked: false,
            scales: {
                yRecency: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: (features[0] ? (features[0].charAt(0).toUpperCase()+features[0].slice(1)) : 'Feature 1') + (features[0] === 'recency' ? ' (hari)' : (features[0] === 'spending' ? ' (Rp)' : '')) }
                },
                yFreq: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    title: { display: true, text: (features[1] ? (features[1].charAt(0).toUpperCase()+features[1].slice(1)) : 'Feature 2') }
                },
                yMoney: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    title: { display: true, text: (features[2] ? (features[2].charAt(0).toUpperCase()+features[2].slice(1)) : 'Feature 3') + (features[2] === 'spending' ? ' (Rp)' : '') },
                    // offset the axis to avoid overlap
                    offset: true
                }
            }
        }
    });

    // Product Type Distribution Charts
    @php
        $productTypeData = $productTypes ?? [];
    @endphp
    
    // Scatter chart data (Frequency vs Total Spending)
    @php
        $clusterMembers = $cluster->clusterMembers ?? collect();
        // prepare points: x = frequency, y = total_spent, cluster = cluster_number, label = customer name
        $scatterPoints = $clusterMembers->map(function($m){
            return [
                'x' => (float) ($m->frequency ?? 0),
                'y' => (float) ($m->total_spent ?? 0),
                'cluster' => (int) ($m->cluster_number ?? 0),
                'label' => $m->customer->name ?? ''
            ];
        })->values()->toArray();

        // counts per cluster (use string keys for safety)
        $clusterCounts = $clusterMembers->groupBy(function($m){ return (int) ($m->cluster_number ?? 0); })->map->count()->toArray();

        // deterministic HSL palette per cluster index 1..k
        $k_val = max(1, (int) ($cluster->k_value ?? 1));
        $clusterColors = [];
        for ($i = 1; $i <= $k_val; $i++) {
            $h = (int) ((($i - 1) * (360 / max(1, $k_val))) % 360);
            $clusterColors[$i] = "hsl({$h}, 70%, 45%)";
        }
    @endphp

    <script>
        // Prepare scatter data and legend
        var scatterPoints = @json($scatterPoints);
        var clusterCounts = @json($clusterCounts);
        var clusterColors = @json($clusterColors);

        // Build per-point color array
        var pointColors = scatterPoints.map(function(p){
            var c = clusterColors[p.cluster] || 'rgba(128,128,128,0.6)';
            // Convert hsl(...) to a semi-transparent value if needed
            if (c.indexOf('hsl(') === 0) {
                return c.replace(')', ', 0.85)').replace('hsl(', 'hsla(');
            }
            return c;
        });

        // Render legend into #clusterLegend
        (function renderLegend(){
            var legend = document.getElementById('clusterLegend');
            if (!legend) return;
            legend.innerHTML = '';
            Object.keys(clusterColors).forEach(function(key){
                var idx = parseInt(key, 10);
                var color = clusterColors[key];
                var count = (clusterCounts[idx] !== undefined) ? clusterCounts[idx] : 0;
                var item = document.createElement('div');
                item.className = 'd-flex align-items-center';
                item.style.gap = '8px';
                item.innerHTML = '<span style="display:inline-block;width:18px;height:12px;background:' + color + ';border-radius:3px;border:1px solid rgba(0,0,0,0.08);"></span>' +
                                 '&nbsp;<small>Kelompok ' + idx + ' — <strong>' + count + '</strong></small>';
                legend.appendChild(item);
            });
        })();

        // Create scatter chart
        (function(){
            var ctx = document.getElementById('scatterChart');
            if (!ctx) return;
            var scatterConfig = {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'Pelanggan',
                        data: scatterPoints,
                        backgroundColor: pointColors,
                        borderColor: 'rgba(0,0,0,0.06)',
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        x: {
                            title: { display: true, text: 'Frekuensi (jumlah transaksi)' },
                            beginAtZero: true
                        },
                        y: {
                            title: { display: true, text: 'Total Spending (Rp)' },
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context){
                                    var p = context.raw || {};
                                    var label = p.label || '';
                                    var x = p.x || 0;
                                    var y = p.y || 0;
                                    return (label ? (label + ': ') : '') + 'F=' + x + ', Total=Rp ' + Number(y).toLocaleString();
                                }
                            }
                        },
                        legend: { display: false }
                    }
                }
            };

            new Chart(ctx.getContext('2d'), scatterConfig);
        })();

        // Time-series chart for revenue per cluster (if available)
        (function(){
            var ts = @json($timeSeries ?? []);
            if (!ts || !ts.labels || !ts.datasets) return;
            var labels = ts.labels;
            var datasets = ts.datasets.map(function(d, idx){
                var h = (idx * (360 / Math.max(1, @json($k_val ?? 1)))) % 360;
                return {
                    label: d.label || ('Cluster ' + (idx+1)),
                    data: d.data || [],
                    borderColor: 'hsl(' + h + ', 70%, 45%)',
                    backgroundColor: 'hsla(' + h + ', 70%, 45%, 0.12)',
                    fill: true,
                    tension: 0.2
                };
            });
            var ctxTs = document.getElementById('timeSeriesChart');
            if (!ctxTs) return;
            new Chart(ctxTs.getContext('2d'), {
                type: 'line',
                data: { labels: labels, datasets: datasets },
                options: { responsive: true, maintainAspectRatio: true, interaction: { mode: 'nearest', intersect: false }, scales: { y: { beginAtZero: true } } }
            });
        })();
    </script>
    @for ($i = 1; $i <= $k; $i++)
        @php
            $types = array_keys($productTypeData[$i] ?? []);
            $percentages = array_values($productTypeData[$i] ?? []);
        @endphp
        var productTypeCtx{{ $i }} = document.getElementById('productTypeChart{{ $i }}').getContext('2d');
        new Chart(productTypeCtx{{ $i }}, {
            type: 'doughnut',
            data: {
                labels: @json($types),
                datasets: [{
                    data: @json($percentages),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: 'Persentase Jenis Produk'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.formattedValue || '';
                                return label + ': ' + value + '%';
                            }
                        }
                    }
                }
            }
        });
    @endfor

    // Label editing functionality
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal(document.getElementById('editLabelModal'));
        const editClusterNumber = document.getElementById('editClusterNumber');
        const editLabel = document.getElementById('editLabel');
        const saveLabelBtn = document.getElementById('saveLabelBtn');
        const clusterId = {{ $cluster->id }};

        document.querySelectorAll('.edit-label-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const clusterNum = this.dataset.cluster;
                const currentLabel = this.dataset.current;
                editClusterNumber.value = clusterNum;
                editLabel.value = currentLabel;
                modal.show();
            });
        });

        saveLabelBtn.addEventListener('click', function() {
            const formData = new FormData();
            formData.append('cluster_number', editClusterNumber.value);
            formData.append('label', editLabel.value);
            formData.append('_token', '{{ csrf_token() }}');

            fetch(`/clustering/${clusterId}/update-label`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const labelSpan = document.querySelector(`.cluster-label[data-cluster="${editClusterNumber.value}"]`);
                    if (labelSpan) {
                        labelSpan.innerHTML = ` - ${data.label}`;
                    }
                    const btn = document.querySelector(`.edit-label-btn[data-cluster="${editClusterNumber.value}"]`);
                    if (btn) {
                        btn.dataset.current = data.label;
                    }
                    modal.hide();

                    // Update table labels
                    document.querySelectorAll(`td small[data-cluster="${editClusterNumber.value}"]`).forEach(el => {
                        el.textContent = data.label;
                    });

                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.innerHTML = `
                        Label berhasil diperbarui
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid').firstChild);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Show error message
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show';
                alert.innerHTML = `
                    Terjadi kesalahan saat memperbarui label
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid').firstChild);
            });
        });
    });
</script>
@endsection
