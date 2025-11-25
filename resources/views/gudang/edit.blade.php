@extends('layouts.app')

@section('title','Edit Stok Gudang')

@section('content')
<div class="container">
    <h3>Edit Stok</h3>
    <form action="{{ route('gudang.update', $item->id) }}" method="post">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Nama Produk</label>
            <input type="text" name="product_type" class="form-control" value="{{ $item->product_type }}" required />
        </div>
        <div class="mb-3">
            <label class="form-label">Warna</label>
            <input type="text" name="color" class="form-control" value="{{ $item->color }}" />
        </div>
        <div class="mb-3">
            <label class="form-label">Ukuran</label>
            <input type="text" name="size" class="form-control" value="{{ $item->size }}" />
        </div>
        <div class="mb-3">
            <label class="form-label">Kategori</label>
            <input type="text" name="category" class="form-control" value="{{ $item->category }}" />
        </div>
        <div class="mb-3">
            <label class="form-label">Qty</label>
            <input type="number" name="qty" class="form-control" value="{{ $item->qty }}" min="0" />
        </div>
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('gudang.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
