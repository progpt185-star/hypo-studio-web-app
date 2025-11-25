@extends('layouts.app')

@section('title','Gudang - Daftar Stok')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Manajemen Gudang</h3>
        @if(\Illuminate\Support\Facades\Route::has('gudang.create'))
            <a href="{{ route('gudang.create') }}" class="btn btn-primary">Tambah Stok</a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Produk</th>
                            <th>Warna</th>
                            <th>Ukuran</th>
                            <th>Kategori</th>
                            <th>Qty</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $it)
                        <tr>
                            <td>{{ $it->id }}</td>
                            <td>{{ $it->product_type }}</td>
                            <td>{{ $it->color }}</td>
                            <td>{{ $it->size }}</td>
                            <td>{{ $it->category }}</td>
                            <td>{{ $it->qty }}</td>
                            <td>
                                @if(\Illuminate\Support\Facades\Route::has('gudang.edit'))
                                    <a href="{{ route('gudang.edit', $it->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                @endif
                                @if(\Illuminate\Support\Facades\Route::has('gudang.destroy'))
                                    <form action="{{ route('gudang.destroy', $it->id) }}" method="post" class="d-inline-block" onsubmit="return confirm('Hapus item?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $items->links() }}</div>
        </div>
    </div>
</div>
@endsection
