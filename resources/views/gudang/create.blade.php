@extends('layouts.app')

@section('title','Tambah Stok Gudang')

@section('content')
<div class="container">
    <h3>Tambah Stok</h3>
    <form action="{{ route('gudang.store') }}" method="post">
        @csrf
        <div class="mb-3">
            <label class="form-label">Nama Produk</label>
            <input type="text" name="product_type" class="form-control" required />
        </div>
        <div class="mb-3">
            <label class="form-label">Warna</label>
            <input type="text" name="color" class="form-control" />
        </div>
        <div class="mb-3">
            <label class="form-label">Ukuran</label>
            <input type="text" name="size" class="form-control" />
        </div>
        <div class="mb-3">
            <label class="form-label">Kategori</label>
            <input type="text" name="category" class="form-control" />
        </div>
        <div class="mb-3">
            <label class="form-label">Qty</label>
            <input type="number" name="qty" class="form-control" value="0" min="0" />
        </div>
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('gudang.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
