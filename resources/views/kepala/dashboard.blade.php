@extends('admin.layouts.app')

@section('title', 'Dashboard Kepala TPU')

@push('styles')
<style>
    .kepala-dashboard-shell {
        position: relative;
    }

    .kepala-hero {
        background: linear-gradient(135deg, #102844 0%, #1E3E62 55%, #2b5889 100%);
        color: #fff;
        border-radius: 26px;
        box-shadow: 0 18px 44px rgba(16, 40, 68, 0.18);
        overflow: hidden;
        position: relative;
    }

    .kepala-hero::after {
        content: '';
        position: absolute;
        inset: auto -90px -90px auto;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
    }

    .kepala-hero-title {
        font-weight: 800;
        letter-spacing: -.04em;
        line-height: 1.05;
    }

    .kepala-hero-text {
        max-width: 60rem;
        opacity: .9;
    }

    .kepala-stat-card {
        height: 100%;
        border: 2px solid #111827;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 12px 0 rgba(17, 24, 39, 0.08);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .kepala-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 0 rgba(17, 24, 39, 0.10);
    }

    .kepala-stat-value {
        font-size: 2rem;
        font-weight: 900;
        letter-spacing: -.04em;
        color: #101828;
    }

    .kepala-tpu-card {
        height: 100%;
        border: 2px solid #111827;
        border-radius: 24px;
        background: #fff;
        box-shadow: 0 12px 0 rgba(17, 24, 39, 0.08);
        overflow: hidden;
    }

    .kepala-tpu-header {
        padding: 1rem 1.15rem;
        background: linear-gradient(135deg, #f8fafc 0%, #eef4fb 100%);
        border-bottom: 2px solid #111827;
    }

    .kepala-tpu-title {
        font-weight: 800;
        letter-spacing: -.03em;
        color: #101828;
        margin-bottom: .15rem;
    }

    .kepala-tpu-desc {
        color: #475467;
        margin-bottom: 0;
        font-size: .95rem;
    }

    .kepala-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .4rem .7rem;
        border-radius: 999px;
        border: 1.5px solid #111827;
        font-size: .8rem;
        font-weight: 700;
        white-space: nowrap;
        background: #f8fafc;
        color: #344054;
    }

    .kepala-chip-primary {
        background: #ecf2ff;
        color: #1E3E62;
    }

    .kepala-chip-success {
        background: #e8fff2;
        color: #027a48;
    }

    .kepala-chip-warning {
        background: #fef3c7;
        color: #b45309;
    }

    .kepala-chip-danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .kepala-tpu-body {
        padding: 1.15rem;
    }

    .kepala-mini-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }

    .kepala-mini-box {
        padding: .9rem;
        border-radius: 16px;
        background: #f8fafc;
        border: 1.5px solid #e5e7eb;
    }

    .kepala-mini-label {
        display: block;
        color: #667085;
        font-size: .78rem;
        font-weight: 700;
        margin-bottom: .35rem;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .kepala-mini-value {
        font-size: 1.35rem;
        font-weight: 800;
        color: #101828;
        letter-spacing: -.03em;
    }

    .kepala-action-card {
        border: 2px solid #111827;
        border-radius: 24px;
        background: #fff;
        box-shadow: 0 12px 0 rgba(17, 24, 39, 0.08);
    }

    .kepala-action-link {
        border: 2px solid #1E3E62;
        background: #1E3E62;
        color: #fff;
        font-weight: 800;
        border-radius: 14px;
        padding: .75rem 1.2rem;
        text-decoration: none !important;
    }

    .kepala-action-link:hover {
        color: #fff;
        background: #152d47;
        border-color: #152d47;
    }

    @media (max-width: 767.98px) {
        .kepala-mini-grid {
            grid-template-columns: 1fr;
        }

        .kepala-hero {
            border-radius: 20px;
        }

        .kepala-tpu-card,
        .kepala-stat-card,
        .kepala-action-card {
            border-radius: 18px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4 kepala-dashboard-shell">
    <div class="kepala-hero p-4 p-lg-5 mb-4">
        <div class="position-relative">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
                <div class="pe-lg-4">
                    <h1 class="kepala-hero-title mb-2">Dashboard Kepala TPU</h1>
                    <p class="kepala-hero-text mb-0">
                        Pantau data pemakaman dari seluruh TPU secara rinci, lihat deskripsi tiap lokasi, dan kelola layanan pemakaman dari satu akun pusat.
                    </p>
                </div>
                <div class="text-lg-end">
                    <div class="d-flex flex-column align-items-lg-end gap-2">
                        <span class="kepala-chip">
                            <i class="bi bi-person-badge"></i>
                            Akun pusat kepala
                        </span>
                        <span class="kepala-chip" style="background: rgba(255,255,255,.12); color:#fff; border-color: rgba(255,255,255,.25);">
                            <i class="bi bi-calendar3"></i>
                            {{ now()->translatedFormat('l, d F Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="kepala-stat-card p-4">
                <p class="text-muted mb-1">Total Jenazah</p>
                <div class="kepala-stat-value">{{ $totalJenazah ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kepala-stat-card p-4">
                <p class="text-muted mb-1">Total Makam</p>
                <div class="kepala-stat-value">{{ $totalMakam ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kepala-stat-card p-4">
                <p class="text-muted mb-1">Total Permohonan</p>
                <div class="kepala-stat-value">{{ $totalPermohonan ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kepala-stat-card p-4">
                <p class="text-muted mb-1">Total TPU</p>
                <div class="kepala-stat-value">{{ $totalTpu ?? 0 }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="kepala-stat-card p-4">
                <p class="text-muted mb-1">Menunggu</p>
                <div class="kepala-stat-value">{{ $permohonanPending ?? 0 }}</div>
                <span class="kepala-chip kepala-chip-warning mt-3">
                    <i class="bi bi-hourglass-split"></i>
                    Status proses
                </span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kepala-stat-card p-4">
                <p class="text-muted mb-1">Disetujui</p>
                <div class="kepala-stat-value text-success">{{ $permohonanDisetujui ?? 0 }}</div>
                <span class="kepala-chip kepala-chip-success mt-3">
                    <i class="bi bi-check2-circle"></i>
                    Siap dipantau
                </span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kepala-stat-card p-4">
                <p class="text-muted mb-1">Ditolak</p>
                <div class="kepala-stat-value text-danger">{{ $permohonanDitolak ?? 0 }}</div>
                <span class="kepala-chip kepala-chip-danger mt-3">
                    <i class="bi bi-x-circle"></i>
                    Perlu tindak lanjut
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        @foreach(($tpuSummaries ?? []) as $summary)
            <div class="col-lg-4">
                <div class="kepala-tpu-card">
                    <div class="kepala-tpu-header">
                        <h5 class="kepala-tpu-title">{{ $summary['name'] }}</h5>
                        <p class="kepala-tpu-desc">{{ $summary['description'] }}</p>
                    </div>
                    <div class="kepala-tpu-body">
                        <div class="kepala-mini-grid mb-3">
                            <div class="kepala-mini-box">
                                <span class="kepala-mini-label">Jenazah</span>
                                <div class="kepala-mini-value">{{ $summary['jenazah'] }}</div>
                            </div>
                            <div class="kepala-mini-box">
                                <span class="kepala-mini-label">Makam</span>
                                <div class="kepala-mini-value">{{ $summary['makam'] }}</div>
                            </div>
                            <div class="kepala-mini-box">
                                <span class="kepala-mini-label">Permohonan</span>
                                <div class="kepala-mini-value">{{ $summary['permohonan'] }}</div>
                            </div>
                            <div class="kepala-mini-box">
                                <span class="kepala-mini-label">Menunggu</span>
                                <div class="kepala-mini-value">{{ $summary['pending'] }}</div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="kepala-chip kepala-chip-primary">
                                <i class="bi bi-check2-circle"></i>
                                {{ $summary['approved'] }} disetujui
                            </span>
                            <span class="kepala-chip kepala-chip-danger">
                                <i class="bi bi-x-circle"></i>
                                {{ $summary['rejected'] }} ditolak
                            </span>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="{{ route('kepala.data-jenazah', ['tpu' => $summary['name']]) }}" class="kepala-action-link text-center">
                                Lihat Data Jenazah
                            </a>
                            <a href="{{ route('kepala.data-makam', ['tpu' => $summary['name']]) }}" class="kepala-action-link text-center">
                                Lihat Data Makam
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="kepala-action-card p-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
            <div>
                <h5 class="fw-bold mb-1">Akses Cepat</h5>
                <p class="text-muted mb-0">Kelola data petugas, makam, dan laporan dari satu akun kepala pusat.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('kepala.users.index') }}" class="kepala-action-link">Kelola User</a>
                <a href="{{ route('kepala.data-makam') }}" class="kepala-action-link">Data Makam</a>
                <a href="{{ route('kepala.laporan') }}" class="kepala-action-link">Laporan</a>
            </div>
        </div>
    </div>
</div>
@endsection
