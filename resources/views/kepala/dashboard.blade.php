@extends('admin.layouts.app')

@section('title', 'Dashboard Kepala UPT')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Dashboard Kepala UPT</h4>
            <p class="text-muted mb-0">Monitoring laporan dan aktivitas TPU.</p>
        </div>
        <span class="badge px-3 py-2" style="background-color: #1E3E62; color: white;">
            {{ now()->translatedFormat('l, d F Y') }}
        </span>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Jenazah</p>
                    <h3 class="fw-bold mb-0">{{ $totalJenazah }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Makam</p>
                    <h3 class="fw-bold mb-0">{{ $totalMakam }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Permohonan</p>
                    <h3 class="fw-bold mb-0">{{ $totalPermohonan }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Menunggu</p>
                    <h3 class="fw-bold mb-0">{{ $permohonanPending }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-1">Laporan Pemakaman</h6>
                <p class="text-muted mb-0">Akses ringkasan data pemakaman harian, mingguan, bulanan, dan tahunan.</p>
            </div>
            <a href="{{ route('kepala.laporan') }}" class="btn btn-success">Lihat Laporan</a>
        </div>
    </div>
</div>
@endsection
