@extends('layouts.app')

@section('title', 'Detail Pelanggan - Hypo Studio')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('customers.index') }}" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <h1 class="h3 mb-0">
                <i class="fas fa-user"></i> Detail Pelanggan: {{ $customer->name }}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="card-title">Informasi Pelanggan</h5>
                    <hr>
                    <p class="mb-2">
                        <strong>Nama:</strong><br>
                        {{ $customer->name }}
                    </p>
                    <p class="mb-2">
                        <strong>No. HP:</strong><br>
                        {{ $customer->phone }}
                    </p>
                    <p class="mb-2">
                        <strong>Email:</strong><br>
                        {{ $customer->email ?? '-' }}
                    </p>
                    <p class="mb-2">
                        <strong>Alamat:</strong><br>
                        {{ $customer->address }}
                    </p>
                    <p class="mb-2">
                        <strong>Terdaftar:</strong><br>
                        {{ optional($customer->created_at)->format('d M Y H:i') ?? '-' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="card-title">Statistik Pembelian</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <p class="text-muted mb-1">Total Pesanan</p>
                                <h3 class="mb-0 text-primary">{{ $customer->total_orders }}</h3>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <p class="text-muted mb-1">Total Pembelian</p>
                                <h3 class="mb-0 text-success">Rp {{ number_format($customer->total_spent, 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Riwayat Pesanan</h5>
                </div>
                <div class="card-body">
                    @if ($customer->orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Produk</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($customer->orders as $order)
                                        <tr>
                                            <td>{{ optional($order->order_date)->format('d M Y') ?? '-' }}</td>
                                            <td>{{ $order->product_type }}</td>
                                            <td><span class="badge bg-info">{{ $order->quantity }}</span></td>
                                            <td>
                                                <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            Belum ada pesanan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <a href="{{ route('customers.edit', ['customer' => $customer->id]) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form action="{{ route('customers.destroy', ['customer' => $customer->id]) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
