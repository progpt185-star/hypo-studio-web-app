@extends('layouts.app')

@section('title', (isset($order) ? 'Edit' : 'Tambah') . ' Pesanan - Hypo Studio')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="{{ route('orders.index') }}" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <h1 class="h3 mb-0">
                <i class="fas fa-{{ isset($order) ? 'edit' : 'plus' }}"></i>
                {{ isset($order) ? 'Edit' : 'Tambah' }} Pesanan
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ isset($order) ? route('orders.update', $order) : route('orders.store') }}" method="POST">
                        @csrf
                        @if (isset($order))
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Pelanggan <span class="text-danger">*</span></label>
                            <select class="form-select @error('customer_id') is-invalid @enderror" 
                                    id="customer_id" name="customer_id" required>
                                <option value="">Pilih Pelanggan</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                        {{ old('customer_id', $order->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="order_date" class="form-label">Tanggal Pesanan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('order_date') is-invalid @enderror" 
                                   id="order_date" name="order_date" 
                                   value="{{ old('order_date', isset($order) ? $order->order_date->format('Y-m-d') : '') }}" required>
                            @error('order_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="product_type" class="form-label">Jenis Produk <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('product_type') is-invalid @enderror" 
                                   id="product_type" name="product_type" placeholder="Contoh: Paket A, Paket B" 
                                   value="{{ old('product_type', $order->product_type ?? '') }}" required>
                            @error('product_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="color" class="form-label">Warna</label>
                                <input type="text" class="form-control @error('color') is-invalid @enderror" 
                                       id="color" name="color" placeholder="Contoh: White, Black" 
                                       value="{{ old('color', $order->color ?? '') }}">
                                @error('color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="size" class="form-label">Ukuran</label>
                                <input type="text" class="form-control @error('size') is-invalid @enderror" 
                                       id="size" name="size" placeholder="Contoh: S, M, L" 
                                       value="{{ old('size', $order->size ?? '') }}">
                                @error('size')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="category" class="form-label">Kategori Lengan</label>
                                <select class="form-select @error('category') is-invalid @enderror" id="category" name="category">
                                    <option value="">Pilih</option>
                                    <option value="lengan pendek" {{ old('category', $order->category ?? '') == 'lengan pendek' ? 'selected' : '' }}>Lengan pendek</option>
                                    <option value="lengan panjang" {{ old('category', $order->category ?? '') == 'lengan panjang' ? 'selected' : '' }}>Lengan panjang</option>
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="quantity" class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                   id="quantity" name="quantity" min="1" placeholder="Masukkan jumlah" 
                                   value="{{ old('quantity', $order->quantity ?? '') }}" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="total_price" class="form-label">Total Harga <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control @error('total_price') is-invalid @enderror" 
                                       id="total_price" name="total_price" step="0.01" placeholder="Masukkan total harga" 
                                       value="{{ old('total_price', $order->total_price ?? '') }}" required>
                            </div>
                            @error('total_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
