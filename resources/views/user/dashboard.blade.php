@extends('admin.layouts.app')

@section('title', 'Dashboard Ahli Waris')

@push('styles')
<style>
    .user-dashboard-shell {
        position: relative;
    }

    .user-hero {
        background: #ffffff;
        border: 2px solid #1E3E62;
        border-radius: 24px;
        box-shadow: 0 16px 40px rgba(30, 62, 98, 0.10);
        overflow: hidden;
    }

    .user-hero::after {
        content: '';
        position: absolute;
        inset: auto -120px -120px auto;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: rgba(30, 62, 98, 0.08);
        pointer-events: none;
    }

    .user-hero-copy {
        position: relative;
        z-index: 1;
    }

    .user-eyebrow {
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

    .user-hero-title {
        font-weight: 800;
        color: #101828;
        line-height: 1.1;
        letter-spacing: -.03em;
    }

    .user-hero-text {
        color: #475467;
        max-width: 58rem;
    }

    .user-role-badge {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .6rem .9rem;
        border-radius: 999px;
        border: 2px solid #1E3E62;
        background: #1E3E62;
        color: #fff;
        font-weight: 800;
        box-shadow: 0 10px 0 rgba(30, 62, 98, 0.14);
    }

    .user-stat-card {
        height: 100%;
        border: 2px solid #111827;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 12px 0 rgba(17, 24, 39, 0.08);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .user-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 0 rgba(17, 24, 39, 0.10);
        border-color: #1E3E62;
    }

    .user-stat-icon {
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

    .user-card-link {
        display: block;
        color: inherit;
        text-decoration: none;
        height: 100%;
    }

    .user-tpu-card {
        height: 100%;
        border: 2px solid #111827;
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 12px 0 rgba(17, 24, 39, 0.08);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        overflow: hidden;
    }

    .user-card-link:hover .user-tpu-card {
        transform: translateY(-5px);
        box-shadow: 0 20px 0 rgba(17, 24, 39, 0.11);
        border-color: #1E3E62;
    }

    .user-tpu-card-header {
        background: #1E3E62;
        color: #fff;
        padding: 1rem 1rem .85rem;
        border-bottom: 2px solid #111827;
    }

    .user-tpu-card-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .35rem .65rem;
        border-radius: 999px;
        border: 1.5px solid rgba(255,255,255,.55);
        background: rgba(255,255,255,.12);
        color: #fff;
        font-size: .8rem;
        font-weight: 700;
    }

    .user-tpu-card-body {
        padding: 1rem;
    }

    .user-tpu-card p {
        color: #475467;
    }

    .user-section-title {
        font-weight: 800;
        color: #101828;
        letter-spacing: -.02em;
        margin-bottom: 0;
    }

    .user-section-card {
        border: 2px solid #111827;
        border-radius: 24px;
        background: #fff;
        box-shadow: 0 12px 0 rgba(17, 24, 39, 0.08);
        overflow: hidden;
    }

    .user-section-header {
        background: #f8fafc;
        border-bottom: 2px solid #111827;
        padding: 1rem 1.15rem;
    }

    .user-table thead th {
        background: #1E3E62;
        color: #fff;
        border-color: #111827;
        font-weight: 700;
        white-space: nowrap;
    }

    .user-table tbody td {
        vertical-align: middle;
        border-color: #d0d5dd;
        color: #344054;
    }

    .user-table tbody tr:hover {
        background: #f8fbff;
    }

    .user-pill {
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

    .user-pill-success {
        background: #e8fff2;
        color: #027a48;
    }

    .user-pill-primary {
        background: #ecf2ff;
        color: #1E3E62;
    }

    .user-pill-secondary {
        background: #f2f4f7;
        color: #344054;
    }

    .user-empty-state {
        padding: 2rem 1rem;
        color: #667085;
    }

    .user-cta {
        border: 2px solid #111827;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 12px 0 rgba(17, 24, 39, 0.08);
        padding: 1.1rem;
    }

    .user-cta .btn {
        border: 2px solid #111827;
        font-weight: 800;
        border-radius: 14px;
        box-shadow: 0 8px 0 rgba(17, 24, 39, 0.08);
    }

    .user-divider {
        border-top: 2px dashed #d0d5dd;
        opacity: 1;
    }

    @media (max-width: 767.98px) {
        .user-hero-title {
            font-size: 1.7rem;
        }

        .user-hero {
            border-radius: 20px;
        }

        .user-section-card,
        .user-stat-card,
        .user-tpu-card,
        .user-cta {
            border-radius: 18px;
        }
    }
</style>
@endpush

@section('content')
@php
    $totalTpu = is_countable($daftarTpu) ? count($daftarTpu) : 0;
    $totalPermohonan = $totalPermohonan ?? ($permohonanSaya->count() ?? 0);
    $permohonanMenunggu = $permohonanMenunggu ?? ($permohonanSaya->where('status', 'menunggu')->count() ?? 0);
    $permohonanDisetujui = $permohonanDisetujui ?? ($permohonanSaya->where('status', 'disetujui')->count() ?? 0);
@endphp

<div class="container-fluid py-4 user-dashboard-shell" id="top">
    <div class="user-hero position-relative mb-4 p-4 p-lg-5">
        <div class="user-hero-copy">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
                <div class="pe-lg-4">
                    <div class="user-eyebrow mb-3">
                        <i class="bi bi-stars"></i>
                        Dashboard Ahli Waris
                    </div>
                    <h1 class="user-hero-title mb-3">Pantau TPU, ajukan permohonan, dan cek status dengan lebih cepat.</h1>
                    <p class="user-hero-text mb-0">
                        Pilih TPU tujuan terlebih dahulu, lalu ajukan dan pantau status permohonan makam Anda.
                        Semua informasi penting ditata lebih ringkas agar mudah dibaca di desktop maupun mobile.
                    </p>
                </div>

                <div class="text-lg-end">
                    <span class="user-role-badge mb-3">
                        <i class="bi bi-shield-check"></i>
                        {{ strtoupper(auth()->user()->role) }}
                    </span>
                    <div class="d-flex flex-column align-items-lg-end gap-2">
                        <div class="text-muted fw-semibold">
                            <i class="bi bi-person-circle me-1"></i>
                            {{ auth()->user()->name ?? 'Pengguna' }}
                        </div>
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

    <div class="row g-3 g-lg-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="user-stat-card p-3 p-lg-4">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted fw-semibold mb-1">Total TPU</div>
                        <h3 class="mb-1 fw-bold text-dark">{{ $totalTpu }}</h3>
                        <small class="text-muted">Lokasi yang tersedia</small>
                    </div>
                    <div class="user-stat-icon">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="user-stat-card p-3 p-lg-4">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted fw-semibold mb-1">Total Permohonan</div>
                        <h3 class="mb-1 fw-bold text-dark">{{ $totalPermohonan }}</h3>
                        <small class="text-muted">Semua pengajuan Anda</small>
                    </div>
                    <div class="user-stat-icon">
                        <i class="bi bi-envelope-paper-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="user-stat-card p-3 p-lg-4">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted fw-semibold mb-1">Menunggu</div>
                        <h3 class="mb-1 fw-bold text-dark">{{ $permohonanMenunggu }}</h3>
                        <small class="text-muted">Belum diproses</small>
                    </div>
                    <div class="user-stat-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="user-stat-card p-3 p-lg-4">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted fw-semibold mb-1">Disetujui</div>
                        <h3 class="mb-1 fw-bold text-dark">{{ $permohonanDisetujui }}</h3>
                        <small class="text-muted">Status selesai</small>
                    </div>
                    <div class="user-stat-icon">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h4 class="user-section-title">Daftar TPU</h4>
            <p class="text-muted mb-0">Pilih lokasi TPU yang paling sesuai untuk kebutuhan Anda.</p>
        </div>
    </div>

    <div class="row g-3 g-lg-4 mb-4">
        @foreach($daftarTpu as $tpu)
            <div class="col-lg-4 col-md-6">
                <a href="{{ route('user.permohonan.create', ['tpu' => $tpu['nama']]) }}" class="user-card-link">
                    <div class="user-tpu-card">
                        <div class="user-tpu-card-header d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="user-tpu-card-badge mb-2">
                                    <i class="bi bi-map"></i>
                                    TPU
                                </div>
                                <h5 class="mb-1 fw-bold">{{ $tpu['nama'] }}</h5>
                                <small class="opacity-75">{{ $tpu['lokasi'] }}</small>
                            </div>
                            <i class="bi bi-arrow-up-right-circle-fill fs-4"></i>
                        </div>
                        <div class="user-tpu-card-body">
                            <p class="mb-0">{{ $tpu['ringkasan'] }}</p>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="user-section-card">
        <div class="user-section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="user-section-title">Permohonan Saya</h4>
                <p class="text-muted mb-0">Riwayat permohonan yang sudah Anda ajukan.</p>
            </div>
            <span class="user-pill user-pill-secondary">
                <i class="bi bi-file-earmark-text"></i>
                {{ $totalPermohonan }} data
            </span>
        </div>

        <div class="p-3 p-lg-4">
            <div class="table-responsive">
                <table class="table user-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;">No</th>
                            <th>TPU</th>
                            <th>Jenis</th>
                            <th>Detail</th>
                            <th style="width: 140px;">Tanggal</th>
                            <th style="width: 130px;">Status</th>
                                <th>Catatan</th>
                                <th style="width:120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permohonanSaya as $item)
                            @php
                                $status = strtolower($item->status ?? '');
                                $statusLabel = ucfirst(str_replace('_', ' ', $status ?: 'proses'));
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $item->tpu ?? '-' }}</td>
                                <td>
                                    @if($item->jenis_permohonan === 'perpanjangan')
                                        <span class="user-pill user-pill-primary">
                                            <i class="bi bi-arrow-repeat"></i>
                                            Perpanjangan
                                        </span>
                                    @else
                                        <span class="user-pill user-pill-success">
                                            <i class="bi bi-plus-circle"></i>
                                            Makam Baru
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->jenis_permohonan === 'perpanjangan')
                                        <small class="text-muted d-block mb-1">
                                            <i class="bi bi-pin-map-fill"></i> Kode Makam
                                        </small>
                                        {{ $item->makam->kode_makam ?? 'Makam tidak ditemukan' }}
                                        @if($item->tahun_pemakaman)
                                            <small class="text-muted d-block">Tahun {{ $item->tahun_pemakaman }}</small>
                                        @endif
                                    @else
                                        <small class="text-muted d-block mb-1">
                                            <i class="bi bi-person-fill"></i> Nama Jenazah
                                        </small>
                                        {{ $item->nama_jenazah ?? 'Data jenazah tidak ditemukan' }}
                                        @if($item->tanggal_wafat)
                                            <small class="text-muted d-block">Wafat: {{ \Carbon\Carbon::parse($item->tanggal_wafat)->format('d-m-Y') }}</small>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $item->created_at?->format('d-m-Y') }}</td>
                                <td>
                                    @if($status === 'disetujui')
                                        <span class="user-pill user-pill-success">{{ $statusLabel }}</span>
                                    @elseif($status === 'ditolak')
                                        <span class="user-pill user-pill-secondary">{{ $statusLabel }}</span>
                                    @else
                                        <span class="user-pill user-pill-primary">{{ $statusLabel }}</span>
                                    @endif
                                </td>
                                <td>{{ $item->catatan ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('user.permohonan.edit', $item) }}" class="btn btn-sm btn-outline-primary">Detail / Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="user-empty-state text-center">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        Belum ada permohonan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="user-cta mt-4">
        <div class="row g-3 align-items-center">
            <div class="col-lg-8">
                <h5 class="fw-bold mb-1">Butuh pengajuan baru?</h5>
                <p class="text-muted mb-0">
                    Langsung buat permohonan baru atau buka salah satu TPU untuk melihat informasi lokasi terlebih dulu.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="#top" class="btn btn-outline-dark mb-2 mb-lg-0">
                    <i class="bi bi-arrow-up-short me-1"></i>
                    Ke Atas
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
