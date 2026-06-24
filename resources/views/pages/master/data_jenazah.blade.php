@extends('admin.layouts.app')

@section('title', 'Data Jenazah')

@section('content')
@php
    $routePrefix = request()->routeIs('petugas.*')
        ? 'petugas'
        : (request()->routeIs('kepala.*') ? 'kepala' : (request()->routeIs('kdlh.*') ? 'kdlh' : 'admin'));
    $canManage = auth()->user()?->isAdmin() || auth()->user()?->isPetugas();
    $isAdmin = auth()->user()?->isAdmin();
@endphp

<div class="container-fluid pt-2 pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Data Jenazah</h4>
            <p class="text-muted mb-0">
                {{ $isPetugasView ? 'Data jenazah dari permohonan TPU ini, termasuk yang masih dalam proses' : 'Daftar data jenazah dalam sistem' }}
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            @if($canManage)
                <a href="{{ route($routePrefix.'.data-jenazah.create', request()->only('tpu')) }}" class="btn btn-sm me-2" style="background-color:#1E3E62;color:white;">
                    <i class="bi bi-plus-circle"></i> Tambah Jenazah
                </a>
            @endif
            <span class="badge px-3 py-2" style="background-color: #1E3E62; color: white;">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route($routePrefix.'.data-jenazah') }}">
                <div class="row g-2">
                    @if($isAdmin || auth()->user()?->isKepala() || auth()->user()?->isKdlh())
                        <div class="col-md-3">
                            <select name="tpu" class="form-select form-select-sm">
                                <option value="">Semua TPU</option>
                                @foreach($tpuOptions ?? [] as $tpu)
                                    <option value="{{ $tpu }}" @selected(($selectedTpu ?? '') === $tpu)>{{ $tpu }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if($isPetugasView)
                        <div class="col-md-3">
                            <select name="filter" class="form-select form-select-sm">
                                <option value="harian" @selected(($filter ?? 'harian') === 'harian')>Harian</option>
                                <option value="mingguan" @selected(($filter ?? 'harian') === 'mingguan')>Mingguan</option>
                                <option value="bulanan" @selected(($filter ?? 'harian') === 'bulanan')>Bulanan</option>
                            </select>
                        </div>
                        <div class="col-md-{{ ($isAdmin || auth()->user()?->isKepala() || auth()->user()?->isKdlh()) ? 4 : 7 }}">
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Cari nama jenazah, NIK, nama ahli waris, atau no hp..."
                                   value="{{ request('search') }}">
                        </div>
                    @else
                        <div class="col-md-{{ ($isAdmin || auth()->user()?->isKepala() || auth()->user()?->isKdlh()) ? 7 : 10 }}">
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Cari nama, NIK, alamat, atau makam..."
                                   value="{{ request('search') }}">
                        </div>
                    @endif
                    <div class="col-md-2">
                        <button class="btn btn-sm w-100" style="background-color:#1E3E62;color:white;">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($isPetugasView)
        @forelse($permohonanJenazah as $tanggal => $items)
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0">
                            @if(($filter ?? 'harian') === 'mingguan')
                                Data Minggu {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                            @elseif(($filter ?? 'harian') === 'bulanan')
                                Data Bulan {{ \Carbon\Carbon::createFromFormat('Y-m', $tanggal)->translatedFormat('F Y') }}
                            @else
                                Data Masuk {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                            @endif
                        </h6>
                        <small class="text-muted">{{ $items->count() }} data jenazah terverifikasi</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle compact-table mb-0">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Jenazah</th>
                                    <th>Ahli Waris</th>
                                    <th>Makam</th>
                                    <th>Dokumen</th>
                                    @if($canManage)
                                        <th width="180">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td class="text-muted">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $item->nama_jenazah ?: ($item->jenazah->nama ?? '-') }}</div>
                                            <div class="small text-muted">NIK: {{ $item->nik_jenazah ?: ($item->jenazah->nik ?? '-') }}</div>
                                            <div class="small text-muted">Tempat Lahir: {{ $item->tempat_lahir ?: ($item->jenazah->tempat_lahir ?? '-') ?: '-' }}</div>
                                            <div class="small text-muted">Jenis Kelamin: {{ $item->jenis_kelamin ?: ($item->jenazah->jenis_kelamin ?? '-') }}</div>
                                            <div class="small text-muted">Agama: {{ $item->agama ?: ($item->jenazah->agama ?? '-') ?: '-' }}</div>
                                            <div class="small text-muted">
                                                Tanggal Lahir: @if($item->tanggal_lahir || optional($item->jenazah)->tanggal_lahir)
                                                    {{ \Carbon\Carbon::parse($item->tanggal_lahir ?: $item->jenazah->tanggal_lahir)->format('d-m-Y') }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                            <div class="small text-muted">
                                                Tanggal Wafat: @if($item->tanggal_wafat || optional($item->jenazah)->tanggal_wafat)
                                                    {{ \Carbon\Carbon::parse($item->tanggal_wafat ?: $item->jenazah->tanggal_wafat)->format('d-m-Y') }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                            <div class="small text-muted">Alamat: {{ $item->alamat ?: ($item->jenazah->alamat ?? '-') ?: '-' }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $item->nama_ahli_waris ?: '-' }}</div>
                                            <div class="small text-muted">HP: {{ $item->no_hp_ahli_waris ?: '-' }}</div>
                                            <div class="small text-muted">Hubungan: {{ $item->hubungan_keluarga ?: '-' }}</div>
                                            @if($item->catatan)
                                                <div class="small text-muted">Catatan: {{ $item->catatan }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @php $relatedMakam = $item->jenazah->makam ?? $item->makam ?? null; @endphp
                                            <div class="fw-semibold text-dark">{{ $relatedMakam->kode_makam ?? '-' }}</div>
                                            <div class="small text-muted">Blok: {{ $relatedMakam->blok ?? '-' }}</div>
                                            <div class="small text-muted">Zona: {{ $relatedMakam->zona ?? '-' }}</div>
                                            <div class="small text-muted">Nomor: {{ $relatedMakam->nomor_makam ?? $relatedMakam->nomor ?? '-' }}</div>
                                            <div class="small text-muted">Keterangan: {{ $relatedMakam->keterangan ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @if($item->scan_ktp_ahli_waris)
                                                    <a href="{{ asset('storage/'.$item->scan_ktp_ahli_waris) }}" target="_blank" class="btn btn-outline-secondary btn-xs">KTP</a>
                                                @endif
                                                @if($item->scan_kk)
                                                    <a href="{{ asset('storage/'.$item->scan_kk) }}" target="_blank" class="btn btn-outline-secondary btn-xs">KK</a>
                                                @endif
                                                @if($item->surat_kematian)
                                                    <a href="{{ asset('storage/'.$item->surat_kematian) }}" target="_blank" class="btn btn-outline-secondary btn-xs">Surat</a>
                                                @endif
                                                @if(! $item->scan_ktp_ahli_waris && ! $item->scan_kk && ! $item->surat_kematian)
                                                    <span class="small text-muted">Tidak ada dokumen</span>
                                                @endif
                                            </div>
                                        </td>
                                        @if($canManage)
                                            <td>
                                                @if($item->jenazah_id)
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <a href="{{ route($routePrefix.'.data-jenazah.edit', $item->jenazah_id) }}"
                                                           class="btn btn-warning btn-xs">
                                                            <i class="bi bi-pencil-square"></i> Edit
                                                        </a>
                                                        <form action="{{ route($routePrefix.'.data-jenazah.destroy', $item->jenazah_id) }}"
                                                              method="POST"
                                                              onsubmit="return confirm('Yakin hapus data?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-danger btn-xs">
                                                                <i class="bi bi-trash"></i> Hapus
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">Belum tercatat</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body text-center text-muted py-4">
                    Data tidak ditemukan
                </div>
            </div>
        @endforelse
    @else
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle compact-table mb-0">
                        <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Informasi Jenazah</th>
                                    <th>Makam</th>
                                    <th width="170">Tenggat Sewa</th>
                                    <th>Alamat</th>
                                    @if($canManage)
                                        <th width="180">Aksi</th>
                                    @endif
                                </tr>
                        </thead>
                        <tbody>
                            @forelse($jenazah as $item)
                                <tr>
                                    <td class="text-muted">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $item->nama }}</div>
                                        <div class="small text-muted">NIK: {{ $item->nik }}</div>
                                        <div class="small text-muted">
                                            TTL: {{ $item->tempat_lahir ?: '-' }}
                                            @if($item->tanggal_lahir)
                                                , {{ \Carbon\Carbon::parse($item->tanggal_lahir)->format('d-m-Y') }}
                                            @endif
                                        </div>
                                        <div class="small text-muted">
                                            {{ $item->jenis_kelamin ?: '-' }}{{ $item->agama ? ' / '.$item->agama : '' }}
                                        </div>
                                        <div class="small text-muted">
                                            Wafat: {{ $item->tanggal_wafat ? \Carbon\Carbon::parse($item->tanggal_wafat)->format('d-m-Y') : '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $item->makam->kode_makam ?? '-' }}</div>
                                        <div class="small text-muted">
                                            {{ $item->makam->blok ?? '-' }} / {{ $item->makam->zona ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $dueAt = $item->renewalDueAt();
                                            $level = $item->renewalAlertLevel();
                                        @endphp
                                        @if($dueAt)
                                            <div class="fw-semibold {{ $level === 'expired' ? 'text-danger' : ($level === 'soon' ? 'text-warning' : 'text-success') }}">
                                                {{ $dueAt->format('d-m-Y') }}
                                            </div>
                                            @if($level === 'expired')
                                                <span class="badge rounded-pill bg-danger">Lewat batas</span>
                                            @elseif($level === 'soon')
                                                <span class="badge rounded-pill bg-warning text-dark">Mendekati batas</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $item->alamat ?: '-' }}</td>
                                    @if($canManage)
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                <a href="{{ route($routePrefix.'.data-jenazah.edit', $item->id) }}"
                                                   class="btn btn-warning btn-xs">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </a>
                                                <form action="{{ route($routePrefix.'.data-jenazah.destroy', $item->id) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Yakin hapus data?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger btn-xs">
                                                        <i class="bi bi-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canManage ? 6 : 5 }}" class="text-center text-muted py-4">
                                        Data tidak ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .compact-table thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0;
        text-transform: uppercase;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.75rem 0.9rem;
        white-space: nowrap;
    }

    .compact-table tbody td {
        border-color: #eef2f7;
        padding: 0.85rem 0.9rem;
        vertical-align: top;
    }

    .compact-table tbody tr:hover {
        background-color: #fafcff;
    }

    .btn-xs {
        font-size: 0.75rem;
        padding: 0.28rem 0.55rem;
        line-height: 1.2;
    }
</style>
@endpush
