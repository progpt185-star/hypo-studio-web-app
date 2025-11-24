@extends('layouts.app')

@section('title', 'Import Pesanan')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Import Data Pesanan</div>

                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ url('orders/import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label">File CSV / XLSX</label>
                            <input type="file" name="file" id="file" class="form-control" accept=".csv,.xlsx,.xls">
                            @error('file')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('orders.index') }}" class="btn btn-secondary me-2">Batal</a>
                            <button class="btn btn-primary">Upload & Import</button>
                        </div>
                    </form>
                    <hr>
                    <p class="small text-muted">Format kolom yang disarankan: <code>customer_email,customer_name,order_date,product_type,quantity,total_price</code>. Import juga menerima header Bahasa Indonesia seperti <code>nama,tanggal,Jenis Bahan,Harga,Jumlah,total harga</code>. Jika tidak ada email, import akan mencoba mencocokkan berdasarkan nama pelanggan atau membuat pelanggan baru.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
