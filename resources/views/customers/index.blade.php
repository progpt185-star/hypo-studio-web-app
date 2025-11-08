@extends('layouts.app')

@section('title', 'Data Pelanggan - Hypo Studio')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">
                <i class="fas fa-users"></i> Data Pelanggan
            </h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Pelanggan
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <!-- Search -->
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Cari pelanggan..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </form>

            <!-- Table -->
            @if ($customers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama</th>
                                <th>No. HP</th>
                                <th>Email</th>
                                <th width="12%">Total Pesanan</th>
                                <th width="15%">Total Pembelian</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customers as $key => $customer)
                                <tr>
                                    <td>{{ ($customers->currentPage() - 1) * $customers->perPage() + $key + 1 }}</td>
                                    <td>
                        <strong>{{ $customer->name }}</strong>
                    </td>
                                    <td>{{ $customer->phone }}</td>
                                    <td>{{ $customer->email ?? '-' }}</td>
                                    <td>
                        <span class="badge bg-info">{{ $customer->total_orders }}</span>
                    </td>
                                    <td>
                        <strong>Rp {{ number_format($customer->total_spent, 0, ',', '.') }}</strong>
                    </td>
                                    <td>
                        <a href="{{ route('customers.show', ['customer' => $customer->id]) }}" class="btn btn-sm btn-info" title="Lihat">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('customers.edit', ['customer' => $customer->id]) }}" class="btn btn-sm btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('customers.destroy', ['customer' => $customer->id]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" 
                                    onclick="return confirm('Yakin ingin menghapus?')" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">
                                        Tidak ada data pelanggan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $customers->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox" style="font-size: 48px; color: #ccc;"></i>
                    <p class="text-muted mt-3">Belum ada data pelanggan</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
