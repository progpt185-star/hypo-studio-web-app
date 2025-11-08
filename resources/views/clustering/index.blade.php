@extends('layouts.app')

@section('title', 'Analisis K-Means - Hypo Studio')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-network-wired"></i> Analisis K-Means
            </h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ url('/clustering/history') }}" class="btn btn-info">
                <i class="fas fa-history"></i> Riwayat Clustering
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-cog"></i> Jalankan Analisis
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Masukkan jumlah cluster (k) yang Anda inginkan, kemudian klik tombol untuk memulai analisis 
                        segmentasi pelanggan menggunakan algoritma K-Means.
                    </p>

                    <form action="{{ url('/clustering/analyze') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="k" class="form-label">Jumlah Cluster (k)</label>
                            <input type="number" class="form-control @error('k') is-invalid @enderror" 
                                   id="k" name="k" min="2" max="10" value="{{ old('k', 3) }}" required>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle"></i> Masukkan nilai antara 2-10
                            </small>
                            @error('k')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-play"></i> Jalankan Analisis
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Informasi
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Tentang K-Means Clustering:</strong></p>
                    <p>K-Means adalah algoritma pembelajaran mesin yang mengelompokkan data pelanggan 
                       berdasarkan atribut pembelian mereka (frekuensi dan nilai transaksi) ke dalam k cluster.</p>

                    <p><strong>Interpretasi Hasil:</strong></p>
                    <ul>
                        <li><strong>Cluster 1:</strong> Pelanggan Loyal - Frekuensi & nilai transaksi tinggi</li>
                        <li><strong>Cluster 2:</strong> Pelanggan Reguler - Frekuensi & nilai sedang</li>
                        <li><strong>Cluster 3:</strong> Pelanggan Sporadis - Frekuensi & nilai rendah</li>
                    </ul>

                    <p class="text-muted small mt-3">
                        <i class="fas fa-lightbulb"></i> 
                        Tips: Mulai dengan k=3 untuk hasil yang lebih mudah diinterpretasikan.
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if ($lastCluster)
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-check-circle"></i> Analisis Terakhir
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Tanggal Analisis:</strong> {{ $lastCluster->analysis_date->format('d M Y H:i') }}</p>
                                <p><strong>Jumlah Cluster:</strong> {{ $lastCluster->k_value }}</p>
                                <p><strong>Total Pelanggan:</strong> {{ $lastCluster->clusterMembers()->count() }}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="{{ url('/clustering/results/'.$lastCluster->id) }}" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Lihat Hasil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
