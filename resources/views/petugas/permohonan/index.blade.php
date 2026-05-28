@extends('admin.layouts.app')

@section('title', 'Manajemen Permohonan')

@push('styles')
<style>
    .petugas-permohonan-shell {
        position: relative;
    }

    .petugas-hero {
        background: #ffffff;
        border: 2px solid #1E3E62;
        border-radius: 24px;
        box-shadow: 0 16px 40px rgba(30, 62, 98, 0.10);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .petugas-hero::after {
        content: '';
        position: absolute;
        inset: auto -120px -120px auto;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: rgba(30, 62, 98, 0.08);
        pointer-events: none;
    }

    .petugas-hero-copy {
        position: relative;
        z-index: 1;
    }

    .petugas-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .45rem .8rem;
        border-radius: 999px;
        border: 2px solid #1E3E62;
        background: #f8fbff;
        color: #1E3E62;
        font-weight: 700;
        font-size: .82rem;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .petugas-hero-title {
        font-weight: 800;
        color: #101828;
        line-height: 1.1;
        letter-spacing: -.03em;
    }

    .petugas-hero-text {
        color: #475467;
        max-width: 58rem;
    }

    .petugas-stat-card {
        height: 100%;
        border: 2px solid #111827;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 12px 0 rgba(17, 24, 39, 0.08);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .petugas-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 0 rgba(17, 24, 39, 0.10);
        border-color: #1E3E62;
    }

    .petugas-stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        border: 2px solid #111827;
        display: grid;
        place-items: center;
        font-size: 1.2rem;
        color: #111827;
        background: #f4f6f9;
        flex: 0 0 auto;
    }

    .petugas-section-title {
        font-weight: 800;
        color: #101828;
        letter-spacing: -.02em;
        margin-bottom: 0;
    }

    .petugas-section-card {
        border: 2px solid #111827;
        border-radius: 24px;
        background: #fff;
        box-shadow: 0 12px 0 rgba(17, 24, 39, 0.08);
        overflow: hidden;
    }

    .petugas-section-header {
        background: #f8fafc;
        border-bottom: 2px solid #111827;
        padding: 1rem 1.15rem;
    }

    .petugas-table thead th {
        background: #1E3E62;
        color: #fff;
        border-color: #111827;
        font-weight: 700;
        white-space: nowrap;
    }

    .petugas-table tbody td {
        vertical-align: middle;
        border-color: #d0d5dd;
        color: #344054;
    }

    .petugas-table tbody tr:hover {
        background: #f8fbff;
    }

    .petugas-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .42rem .72rem;
        border-radius: 999px;
        border: 1.5px solid #111827;
        font-weight: 700;
        font-size: .82rem;
        white-space: nowrap;
    }

    .petugas-pill-success {
        background: #e8fff2;
        color: #027a48;
    }

    .petugas-pill-warning {
        background: #fef3c7;
        color: #b45309;
    }

    .petugas-pill-danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .petugas-empty-state {
        padding: 2rem 1rem;
        color: #667085;
    }

    .petugas-filter-btn {
        border: 2px solid #111827;
        background: #fff;
        border-radius: 12px;
        padding: .5rem 1rem;
        font-weight: 600;
        font-size: .85rem;
        transition: all .2s ease;
    }

    .petugas-filter-btn:hover,
    .petugas-filter-btn.active {
        background: #1E3E62;
        color: #fff;
        border-color: #1E3E62;
    }

    @media (max-width: 767.98px) {
        .petugas-hero-title {
            font-size: 1.7rem;
        }

        .petugas-hero,
        .petugas-section-card {
            border-radius: 18px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4 petugas-permohonan-shell">
    <div class="petugas-hero position-relative p-4 p-lg-5">
        <div class="petugas-hero-copy">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
                <div class="pe-lg-4">
                    <div class="petugas-eyebrow mb-3">
                        <i class="bi bi-clipboard-check"></i>
                        Manajemen Permohonan
                    </div>
                    <h1 class="petugas-hero-title mb-3">Proses dan kelola semua permohonan makam dari ahli waris.</h1>
                    <p class="petugas-hero-text mb-0">
                        Tinjau permohonan baru, setujui atau tolak, dan berikan catatan untuk ahli waris.
                        Semua permohonan dari TPU {{ $petugas->tpu ?? 'Anda' }} ditampilkan di sini.
                    </p>
                </div>

                <div class="text-lg-end">
                    <div class="text-muted fw-semibold">
                        <i class="bi bi-geo-alt-fill me-1"></i>
                        {{ $petugas->tpu ?? 'TPU' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-2 border-dark shadow-sm mb-4">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    </div>
    @endif

    <!-- Stats Row -->
    <div class="row g-3 g-lg-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="petugas-stat-card p-3 p-lg-4">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted fw-semibold mb-1">Menunggu</div>
                        <h3 class="mb-1 fw-bold text-dark">{{ $stats['menunggu'] }}</h3>
                        <small class="text-muted">Perlu diproses</small>
                    </div>
                    <div class="petugas-stat-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="petugas-stat-card p-3 p-lg-4">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted fw-semibold mb-1">Disetujui</div>
                        <h3 class="mb-1 fw-bold text-dark">{{ $stats['disetujui'] }}</h3>
                        <small class="text-muted">Sudah diproses</small>
                    </div>
                    <div class="petugas-stat-icon">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="petugas-stat-card p-3 p-lg-4">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted fw-semibold mb-1">Ditolak</div>
                        <h3 class="mb-1 fw-bold text-dark">{{ $stats['ditolak'] }}</h3>
                        <small class="text-muted">Penolakan</small>
                    </div>
                    <div class="petugas-stat-icon">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="petugas-stat-card p-3 p-lg-4">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted fw-semibold mb-1">Total</div>
                        <h3 class="mb-1 fw-bold text-dark">{{ $stats['total'] }}</h3>
                        <small class="text-muted">Semua permohonan</small>
                    </div>
                    <div class="petugas-stat-icon">
                        <i class="bi bi-files"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Permohonan List -->
    <div class="petugas-section-card">
        <div class="petugas-section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="petugas-section-title">Daftar Permohonan</h4>
                <p class="text-muted mb-0">Kelola semua pengajuan makam dari ahli waris.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('petugas.permohonan.create') }}" class="btn btn-sm" style="background-color:#1E3E62;color:#fff;">
                    <i class="bi bi-plus-circle"></i> Buat Permohonan
                </a>
                <span class="petugas-pill petugas-pill-warning">
                    <i class="bi bi-file-earmark-text"></i>
                    {{ $stats['total'] }} permohonan
                </span>
            </div>
        </div>

        <div class="p-3 p-lg-4">
            <div class="table-responsive">
                <table class="table petugas-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;">No</th>
                            <th>Jenis</th>
                            <th>Detail</th>
                            <th>Pemohon</th>
                            <th style="width: 140px;">Tanggal</th>
                            <th style="width: 130px;">Status</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permohonans as $item)
                        @php
                        $status = strtolower($item->status ?? '');
                        $statusLabel = match($status) {
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        default => 'Menunggu'
                        };
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $loop->iteration }}</td>
                            <td>
                                @if($item->jenis_permohonan === 'perpanjangan')
                                <span class="petugas-pill petugas-pill-warning">
                                    <i class="bi bi-arrow-repeat"></i>
                                    Perpanjangan
                                </span>
                                @else
                                <span class="petugas-pill petugas-pill-success">
                                    <i class="bi bi-plus-circle"></i>
                                    Makam Baru
                                </span>
                                @endif
                            </td>
                            <td>
                                @if($item->jenis_permohonan === 'perpanjangan')
                                <small class="text-muted">Makam:</small><br>
                                {{ $item->makam?->kode_makam ?? '-' }}
                                @else
                                <small class="text-muted">Jenazah:</small><br>
                                {{ $item->nama_jenazah ?? 'N/A' }}
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $item->user?->name ?? '-' }}</div>
                                <small class="text-muted">{{ $item->nama_ahli_waris ?? '-' }}</small>
                            </td>
                            <td>{{ $item->created_at?->format('d-m-Y') }}</td>
                            <td>
                                @if($status === 'disetujui')
                                <span class="petugas-pill petugas-pill-success">{{ $statusLabel }}</span>
                                @elseif($status === 'ditolak')
                                <span class="petugas-pill petugas-pill-danger">{{ $statusLabel }}</span>
                                @else
                                <span class="petugas-pill petugas-pill-warning">{{ $statusLabel }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('petugas.permohonan.show', $item) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="petugas-empty-state text-center">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    Belum ada permohonan untuk TPU Anda
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection