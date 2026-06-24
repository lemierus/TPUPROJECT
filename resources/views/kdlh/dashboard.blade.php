@extends('admin.layouts.app')

@section('title', 'Dashboard Kepala Dinas')

@section('content')
<div class="container-fluid pt-2 pb-4">
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #17324d 0%, #1e5a7a 100%); color: #fff;">
        <div class="card-body p-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="badge bg-light text-dark mb-3">Kepala Dinas Lingkungan Hidup</span>
                    <h2 class="fw-bold mb-2">Panel Pemantauan TPU Kota Padang</h2>
                    <p class="mb-0" style="max-width: 700px; opacity: .9;">
                        Dari sini kepala dinas dapat memantau seluruh TPU, mengelola akun kepala TPU, serta memperbarui deskripsi tiap TPU agar informasi pada seluruh halaman selalu sinkron.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('kdlh.tpu.index') }}" class="btn btn-light me-2 mb-2">Kelola TPU</a>
                    <a href="{{ route('kdlh.users.index') }}" class="btn btn-outline-light mb-2">Kelola Kepala TPU</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <p class="text-muted mb-1">Total TPU</p>
                    <h3 class="fw-bold mb-0">{{ $totalTpu ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <p class="text-muted mb-1">Kepala TPU</p>
                    <h3 class="fw-bold mb-0">{{ $totalKepalaTpu ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Jenazah</p>
                    <h3 class="fw-bold mb-0">{{ $totalJenazah ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Permohonan</p>
                    <h3 class="fw-bold mb-0">{{ $totalPermohonan ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @foreach(($tpuSummaries ?? []) as $summary)
            <div class="col-lg-4 col-md-6">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold mb-1">{{ $summary['nama'] }}</h5>
                                <small class="text-muted">{{ $summary['lokasi'] }}</small>
                            </div>
                            <span class="badge bg-primary">{{ $summary['jenazah'] }} jenazah</span>
                        </div>
                        <p class="text-muted">{{ $summary['deskripsi'] }}</p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-light text-dark border">{{ $summary['makam'] }} makam</span>
                            <span class="badge bg-light text-dark border">{{ $summary['permohonan'] }} permohonan</span>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <a href="{{ route('kdlh.data-jenazah', ['tpu' => $summary['nama']]) }}" class="btn btn-outline-primary btn-sm">Data Jenazah</a>
                            <a href="{{ route('kdlh.data-makam', ['tpu' => $summary['nama']]) }}" class="btn btn-outline-secondary btn-sm">Data Makam</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
