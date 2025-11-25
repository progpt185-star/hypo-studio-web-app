@extends('layouts.app')

@section('title', 'Laporan Owner')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h1 class="h3">Laporan Owner</h1>
            <p class="text-muted">Halaman monitoring untuk role Owner (view only).</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card p-3">
                <h5>Total Orders</h5>
                <p class="display-6">{{ number_format($totalOrders) }}</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <h5>Total Revenue</h5>
                <p class="display-6">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <h5>Customers</h5>
                <p class="display-6">{{ number_format($customerCount) }}</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <h5>Periode</h5>
                <p class="small">30 hari terakhir</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">Penjualan 30 Hari Terakhir</div>
                <div class="card-body">
                    <canvas id="salesChart" height="120"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Produk Teratas</div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($topProducts as $p)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $p->product_type }}
                                <span class="badge bg-primary">{{ $p->count }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Ringkasan</div>
                <div class="card-body">
                    <p>Total Orders: <strong>{{ number_format($totalOrders) }}</strong></p>
                    <p>Total Revenue: <strong>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function(){
        const data = @json($ordersLast30->map(fn($r) => ['date' => $r->date, 'count' => (int)$r->count, 'revenue' => (float)$r->revenue]));
        const labels = data.map(d => d.date);
        const counts = data.map(d => d.count);
        const revenues = data.map(d => d.revenue);

        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Orders',
                    data: counts,
                    backgroundColor: 'rgba(54,162,235,0.6)'
                }, {
                    label: 'Revenue',
                    data: revenues,
                    type: 'line',
                    borderColor: 'rgba(255,99,132,0.8)',
                    backgroundColor: 'rgba(255,99,132,0.2)',
                    yAxisID: 'y_revenue'
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true },
                    y_revenue: { position: 'right', beginAtZero: true }
                }
            }
        });
    })();
</script>
@endsection
