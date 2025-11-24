@extends('layouts.app')

@section('title', 'Import Pelanggan')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Import Data Pelanggan</div>

                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ url('customers/import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label">File CSV / XLSX</label>
                            <input type="file" name="file" id="file" class="form-control" accept=".csv,.xlsx,.xls">
                            @error('file')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary me-2">Batal</a>
                            <button class="btn btn-primary">Upload & Import</button>
                        </div>
                    </form>
                    <hr>
                    <p class="small text-muted">Format kolom yang disarankan: <code>name,email,phone,address</code> — tetapi import juga menerima variasi Bahasa Indonesia seperti <code>Nama Pelanggan, No. HP, Email, Alamat</code>. Baris tanpa email akan dibuat dengan email placeholder.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
