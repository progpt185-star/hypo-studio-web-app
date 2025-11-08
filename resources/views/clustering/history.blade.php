@extends('layouts.app')

@section('title', 'Riwayat Clustering - Hypo Studio')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-history"></i> Riwayat Clustering
            </h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ url('/clustering') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if ($clusters->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Analisis</th>
                                <th>Tanggal</th>
                                <th width="8%">K Value</th>
                                <th width="12%">Total Pelanggan</th>
                                <th width="18%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clusters as $key => $cluster)
                                <tr>
                                    <td>{{ ($clusters->currentPage() - 1) * $clusters->perPage() + $key + 1 }}</td>
                                    <td><strong>{{ $cluster->name }}</strong></td>
                                    <td>{{ $cluster->analysis_date->format('d M Y H:i') }}</td>
                                    <td>
                        <span class="badge bg-primary">{{ $cluster->k_value }}</span>
                    </td>
                                    <td>
                        {{ $cluster->clusterMembers()->count() }}
                    </td>
                                    <td>
                        <a href="{{ url('/clustering/results/'.$cluster->id) }}" class="btn btn-sm btn-info" title="Lihat">
                            <i class="fas fa-eye"></i> Lihat
                        </a>
                        <button class="btn btn-sm btn-danger" onclick="confirm('Yakin ingin menghapus?')" title="Hapus">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">
                                        Belum ada riwayat clustering
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $clusters->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox" style="font-size: 48px; color: #ccc;"></i>
                    <p class="text-muted mt-3">Belum ada riwayat clustering</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
