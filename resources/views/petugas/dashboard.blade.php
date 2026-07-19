@extends('admin.layouts.app')

@section('title', 'Dashboard Petugas TPU')

@push('styles')
<style>
    .petugas-dashboard-shell {
        position: relative;
    }

    .petugas-hero {
        background: linear-gradient(135deg, #1E3E62 0%, #2d5a8c 100%);
        color: #fff;
        border-radius: 24px;
        box-shadow: 0 16px 40px rgba(30, 62, 98, 0.15);
        overflow: hidden;
        position: relative;
    }

    .petugas-hero::after {
        content: '';
        position: absolute;
        inset: auto -120px -120px auto;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
        pointer-events: none;
    }

    .petugas-hero-copy {
        position: relative;
        z-index: 1;
    }

    .petugas-hero-title {
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -.03em;
    }

    .petugas-hero-text {
        opacity: 0.9;
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

    .petugas-stat-card.approved {
        border-color: #059669;
    }

    .petugas-stat-card.rejected {
        border-color: #dc2626;
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

    .petugas-stat-card.approved .petugas-stat-icon {
        background: #d1fae5;
        border-color: #059669;
        color: #059669;
    }

    .petugas-stat-card.rejected .petugas-stat-icon {
        background: #fee2e2;
        border-color: #dc2626;
        color: #dc2626;
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

    .petugas-pill-danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .petugas-pill-primary {
        background: #ecf2ff;
        color: #1E3E62;
    }

    .petugas-pill-secondary {
        background: #f2f4f7;
        color: #344054;
    }

    .petugas-pill-warning {
        background: #fef3c7;
        color: #b45309;
    }

    .petugas-empty-state {
        padding: 2rem 1rem;
        color: #667085;
    }

    .petugas-btn-process {
        border: 2px solid #1E3E62;
        background: #1E3E62;
        color: #fff;
        font-weight: 800;
        border-radius: 14px;
        padding: .75rem 1.5rem;
        transition: all .18s ease;
        box-shadow: 0 8px 0 rgba(30, 62, 98, 0.08);
        text-decoration: none !important;
    }

    .petugas-btn-process:hover {
        background: #152d47;
        border-color: #152d47;
        transform: translateY(-2px);
        box-shadow: 0 12px 0 rgba(30, 62, 98, 0.10);
        color: #fff;
        text-decoration: none;
    }

    .petugas-btn-inline {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 2px solid #1E3E62;
        background: #1E3E62;
        color: #fff;
        font-weight: 700;
        border-radius: 10px;
        padding: .45rem .8rem;
        font-size: .82rem;
        text-decoration: none !important;
        transition: all .18s ease;
    }

    .petugas-btn-inline:hover {
        background: #152d47;
        border-color: #152d47;
        color: #fff;
        text-decoration: none;
    }

    .petugas-system-info {
        border: 2px solid #111827;
        border-radius: 24px;
        background: #fff;
        box-shadow: 0 12px 0 rgba(17, 24, 39, 0.08);
        padding: 1.5rem;
    }

    .petugas-info-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 12px;
        background: #f8fafc;
        border: 1.5px solid #e5e7eb;
    }

    .petugas-info-icon {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: #1E3E62;
        color: #fff;
        font-size: 1.2rem;
        flex: 0 0 auto;
    }

    @media (max-width: 767.98px) {
        .petugas-hero-title {
            font-size: 1.7rem;
        }

        .petugas-hero {
            border-radius: 20px;
        }

        .petugas-section-card,
        .petugas-stat-card,
        .petugas-system-info {
            border-radius: 18px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4 petugas-dashboard-shell">
    <!-- Hero Section -->
    <div class="petugas-hero p-4 p-lg-5 mb-4">
        <div class="petugas-hero-copy">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
                <div class="pe-lg-4">
                    <h1 class="petugas-hero-title mb-2">Dashboard Petugas {{ $petugas->tpu ?? 'N/A' }}</h1>
                    <p class="petugas-hero-text mb-0">
                        Kelola permohonan pemakaman, pantau status pengajuan, dan proses berkas dengan efisien.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert -->
    @if(session('success'))
        <div class="alert alert-success border-2 border-dark shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(isset($perpanjanganPerluDiingatkan) && $perpanjanganPerluDiingatkan->isNotEmpty())
        <div class="petugas-section-card mb-4">
            <div class="petugas-section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="petugas-section-title">Pengingat Perpanjangan Sewa Makam</h4>
                    <p class="text-muted mb-0">
                        Pengingat ini mengikuti data tenggat sewa pada halaman data jenazah TPU Anda.
                    </p>
                </div>
                <span class="petugas-pill petugas-pill-warning">
                    <i class="bi bi-bell-fill"></i>
                    {{ $perpanjanganPerluDiingatkan->count() }} pengingat
                </span>
            </div>

            <div class="p-3 p-lg-4">
                <div class="table-responsive">
                    <table class="table petugas-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">No</th>
                                <th>Ahli Waris</th>
                                <th>Jenazah</th>
                                <th>TPU</th>
                                <th style="width: 150px;">Batas 2 Tahun</th>
                                <th style="width: 140px;">Status</th>
                                <th style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($perpanjanganPerluDiingatkan as $item)
                                @php
                                    $dueAt = $item->renewalDueAt();
                                    $level = $item->renewalAlertLevel();
                                    $pendingRenewal = $item->pending_renewal_permohonan ?? null;
                                    $targetPermohonan = $item->reminder_target_permohonan ?? null;
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $item->nama_ahli_waris ?? '-' }}</div>
                                        <small class="text-muted">{{ $item->no_hp_ahli_waris ?? '-' }}</small>
                                    </td>
                                    <td>{{ $item->nama ?? '-' }}</td>
                                    <td>{{ $item->tpu ?? '-' }}</td>
                                    <td class="text-nowrap">{{ $dueAt?->format('d-m-Y') ?? '-' }}</td>
                                    <td>
                                        @if($level === 'expired')
                                            <span class="petugas-pill petugas-pill-danger">
                                                <i class="bi bi-exclamation-octagon"></i>
                                                Lewat batas
                                            </span>
                                        @elseif($level === 'soon')
                                            <span class="petugas-pill petugas-pill-warning">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                Mendekat
                                            </span>
                                        @else
                                            <span class="petugas-pill petugas-pill-success">
                                                <i class="bi bi-check2-circle"></i>
                                                Aman
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($targetPermohonan)
                                            <a href="{{ route('petugas.permohonan.show', $targetPermohonan) }}" class="petugas-btn-inline">
                                                <i class="bi bi-arrow-right-circle"></i>
                                                Proses
                                            </a>
                                        @else
                                            <span class="text-muted small">Permohonan tidak ditemukan</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-3 g-lg-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="petugas-stat-card p-3 p-lg-4">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted fw-semibold mb-1">Total Permohonan</div>
                        <h3 class="mb-1 fw-bold text-dark">{{ $totalPermohonan }}</h3>
                        <small class="text-muted">Semua pengajuan</small>
                    </div>
                    <div class="petugas-stat-icon">
                        <i class="bi bi-file-earmark-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="petugas-stat-card approved p-3 p-lg-4">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted fw-semibold mb-1">Disetujui</div>
                        <h3 class="mb-1 fw-bold text-dark">{{ $permohonanDisetujui }}</h3>
                        <small class="text-muted">Status selesai</small>
                    </div>
                    <div class="petugas-stat-icon">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="petugas-stat-card rejected p-3 p-lg-4">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted fw-semibold mb-1">Ditolak</div>
                        <h3 class="mb-1 fw-bold text-dark">{{ $permohonanDitolak }}</h3>
                        <small class="text-muted">Pengajuan ditolak</small>
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
                        <div class="text-muted fw-semibold mb-1">Menunggu Proses</div>
                        <h3 class="mb-1 fw-bold text-dark">{{ $permohonanMenunggu }}</h3>
                        <small class="text-muted">Belum diproses</small>
                    </div>
                    <div class="petugas-stat-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information Section -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h4 class="petugas-section-title">Informasi Sistem</h4>
                <p class="text-muted mb-0">Data master jenazah dan makam dalam sistem</p>
            </div>
        </div>

        <div class="row g-3 g-lg-4">
            <div class="col-md-6">
                <div class="petugas-system-info">
                    <div class="petugas-info-item">
                        <div class="petugas-info-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark">Total Jenazah</div>
                            <p class="mb-0 text-muted">{{ $totalJenazah }} data jenazah terdaftar</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="petugas-system-info">
                    <div class="petugas-info-item">
                        <div class="petugas-info-icon">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark">Total Makam</div>
                            <p class="mb-0 text-muted">{{ $totalMakam }} lokasi makam</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Permohonan Section -->
    <div class="petugas-section-card mb-4">
        <div class="petugas-section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="petugas-section-title">Seluruh Permohonan</h4>
                <p class="text-muted mb-0">Daftar seluruh permohonan yang masuk ke TPU Anda</p>
            </div>
            <a href="{{ route('petugas.permohonan') }}" class="petugas-btn-process">
                <i class="bi bi-arrow-right me-2"></i>Proses Permohonan
            </a>
        </div>

        <div class="p-3 p-lg-4">
                <form action="{{ route('petugas.dashboard') }}" method="GET" class="mb-3">
                    <div class="input-group" style="max-width: 420px;">
                        <span class="input-group-text bg-white border-2" style="border-color:#111827;">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            type="text"
                            name="search"
                            value="{{ $search ?? '' }}"
                            class="form-control border-2"
                            style="border-color:#111827;"
                            placeholder="Cari nama pemohon, jenazah, status..."
                        >
                        @if(!empty($search))
                            <a href="{{ route('petugas.dashboard') }}" class="btn btn-outline-secondary border-2">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                        <button type="submit" class="btn" style="background:#1E3E62;color:#fff;">
                            Cari
                        </button>
                    </div>
                </form>
    <div class="table-responsive">          
            <div class="table-responsive">
                <table class="table petugas-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Pemohon</th>
                            <th>Jenis Permohonan</th>
                            <th style="width: 120px;">Tanggal</th>
                            <th style="width: 140px;">Status</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permohonanTerbaru as $item)
                            @php
                                $status = strtolower($item->status ?? '');
                                $statusLabel = match($status) {
                                    'disetujui' => 'Disetujui',
                                    'ditolak' => 'Ditolak',
                                    'pending' => 'Menunggu',
                                    'menunggu' => 'Menunggu',
                                    default => ucfirst(str_replace('_', ' ', $status ?: 'Proses'))
                                };
                            @endphp
                            <tr>
                                <td class="fw-semibold">
                                    {{ $permohonanTerbaru->firstItem() + $loop->index }}
                                </td>
                                <td class="fw-semibold">{{ $item->nama_pemohon ?? '-' }}</td>
                                <td>
                                    @if($item->jenis_permohonan === 'darurat')
                                        <span class="petugas-pill petugas-pill-danger">
                                            <i class="bi bi-exclamation-diamond"></i>
                                            Darurat
                                        </span>
                                    @elseif($item->jenis_permohonan === 'perpanjangan')
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
                                <td class="text-nowrap">{{ $item->tanggal_permohonan?->format('d-m-Y') ?? $item->created_at?->format('d-m-Y') }}</td>
                                <td>
                                    @if(in_array($status, ['disetujui', 'selesai']))
                                        <span class="petugas-pill petugas-pill-success">{{ $statusLabel }}</span>
                                    @elseif($status === 'ditolak')
                                        <span class="petugas-pill petugas-pill-danger">{{ $statusLabel }}</span>
                                    @elseif(in_array($status, ['pending', 'menunggu', 'menunggu_konfirmasi', 'diproses_darurat', 'menunggu_verifikasi_dokumen', 'perlu_perbaikan_dokumen']))
                                        <span class="petugas-pill petugas-pill-warning">{{ $statusLabel }}</span>
                                    @else
                                        <span class="petugas-pill petugas-pill-secondary">{{ $statusLabel }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('petugas.permohonan.show', $item) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="petugas-empty-state text-center">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        Belum ada permohonan untuk TPU ini
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($permohonanTerbaru->hasPages())
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top">
                    <small class="text-muted">
                        Menampilkan {{ $permohonanTerbaru->firstItem() }} - {{ $permohonanTerbaru->lastItem() }} dari {{ $permohonanTerbaru->total() }} data
                    </small>
                    {{ $permohonanTerbaru->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
