@extends('layouts.app')

@section('title','Owner Dashboard')

@section('content')
<div class="container">
    <h3>Owner Dashboard</h3>
    <p class="text-muted">Halaman ini hanya dapat diakses oleh Owner.</p>

    <div class="card mb-3">
        <div class="card-body">
            <h5>Daftar Owner</h5>
            <ul>
                @foreach($owners as $o)
                    <li>{{ $o->user->name ?? 'User tidak ditemukan' }} (ID: {{ $o->id }})</li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p>Gunakan link <a href="{{ url('/owner/report') }}">Laporan Owner</a> untuk melihat laporan detail.</p>
        </div>
    </div>
</div>
@endsection
