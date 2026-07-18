@extends('admin.layouts.app')

@section('title', 'Permohonan')

@push('styles')
<style>
    .stat-card {
        background: #fff;
        border: 1px solid #eaecf0;
        border-left: 4px solid #98a2b3;
        border-radius: 0 12px 12px 0;
        padding: 1rem 1.1rem;
        height: 100%;
    }

    .stat-card i {
        font-size: 1.3rem;
    }

    .stat-card-label {
        color: #667085;
        font-size: .85rem;
        margin: .5rem 0 .1rem;
    }

    .stat-card-value {
        font-weight: 800;
        margin: 0;
        color: #101828;
    }

    .status-chip {
        border: 2px solid #d0d5dd;
        background: #fff;
        color: #475467;
        border-radius: 999px;
        padding: .35rem .9rem;
        font-size: .82rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: all .15s ease;
    }

    .status-chip:hover {
        border-color: #1E3E62;
        color: #1E3E62;
    }

    .status-chip.active {
        background: #1E3E62;
        border-color: #1E3E62;
        color: #fff;
    }

    .permohonan-table th {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .02em;
        color: #667085;
    }

    .quick-approve-btn,
    .quick-reject-btn {
        border-radius: 8px;
    }

    .dropdown-item.text-danger:hover {
        background-color: #fee4e2;
    }

    .dropdown-item.text-success:hover {
        background-color: #dcfae6;
    }
</style>
@endpush

@section('content')
@php
    $routePrefix = request()->routeIs('petugas.*') ? 'petugas' : 'admin';
    $isAdmin = auth()->user()?->isAdmin();

    $searchTerm = $search ?? request('search');
    $selectedStatus = $selectedStatus ?? request('status');
    $selectedTpuValue = $selectedTpu ?? request('tpu');

    $totalPermohonan = method_exists($permohonans, 'total') ? $permohonans->total() : $permohonans->count();
    $countMenunggu = $permohonans->whereIn('status', ['menunggu', 'pending'])->count();
    $countDisetujui = $permohonans->where('status', 'disetujui')->count();
    $countDitolak = $permohonans->where('status', 'ditolak')->count();

    $statusOptions = [
        '' => 'Semua Status',
        'menunggu' => 'Menunggu',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
    ];
@endphp

<div class="container-fluid pt-2 pb-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Data Permohonan</h4>
            <p class="text-muted mb-0">Daftar permohonan pengajuan makam dari masyarakat</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge px-3 py-2" style="background-color: #1E3E62; color: white;">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
            <a href="{{ route($routePrefix.'.master.permohonan.create', request()->only('tpu')) }}" class="btn btn-sm" style="background-color:#1E3E62;color:white;">
                <i class="bi bi-plus-circle"></i> Tambah Permohonan
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Kartu ringkasan --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card" style="border-left-color:#1E3E62;">
                <i class="bi bi-collection" style="color:#1E3E62;"></i>
                <p class="stat-card-label">Total Permohonan</p>
                <h4 class="stat-card-value">{{ $totalPermohonan }}</h4>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card" style="border-left-color:#b45309;">
                <i class="bi bi-hourglass-split" style="color:#b45309;"></i>
                <p class="stat-card-label">Menunggu</p>
                <h4 class="stat-card-value">{{ $countMenunggu }}</h4>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card" style="border-left-color:#027a48;">
                <i class="bi bi-check-circle" style="color:#027a48;"></i>
                <p class="stat-card-label">Disetujui</p>
                <h4 class="stat-card-value">{{ $countDisetujui }}</h4>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card" style="border-left-color:#dc2626;">
                <i class="bi bi-x-circle" style="color:#dc2626;"></i>
                <p class="stat-card-label">Ditolak</p>
                <h4 class="stat-card-value">{{ $countDitolak }}</h4>
            </div>
        </div>
    </div>

    {{-- Quick status chips --}}
    <div class="d-flex gap-2 flex-wrap mb-3">
        @foreach($statusOptions as $value => $label)
            <a href="{{ route($routePrefix.'.master.permohonan', array_filter(array_merge(request()->query(), ['status' => $value]))) }}"
               class="status-chip {{ (string) $selectedStatus === (string) $value ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Filter: pencarian nama + TPU (khusus admin) --}}
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route($routePrefix.'.master.permohonan') }}">
                @if($selectedStatus)
                    <input type="hidden" name="status" value="{{ $selectedStatus }}">
                @endif
                <div class="row g-3 align-items-end">
                    <div class="col-md-{{ $isAdmin ? 4 : 6 }}">
                        <label class="form-label">Cari Nama Pemohon / Jenazah</label>
                        <input type="text" name="search" value="{{ $searchTerm }}" class="form-control" placeholder="Ketik nama...">
                    </div>

                    @if($isAdmin)
                        <div class="col-md-4">
                            <label class="form-label">TPU</label>
                            <select name="tpu" class="form-select">
                                <option value="">Semua TPU</option>
                                @foreach($tpuOptions ?? [] as $tpu)
                                    <option value="{{ $tpu }}" @selected($selectedTpuValue === $tpu)>{{ $tpu }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-md-{{ $isAdmin ? 4 : 6 }}">
                        <button class="btn w-100" style="background-color:#1E3E62;color:white;">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h6 class="fw-bold mb-0">Data Permohonan</h6>
            <small class="text-muted">Daftar permohonan yang masuk ke sistem</small>
        </div>

        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-bordered align-middle permohonan-table">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>TPU</th>
                            <th>Tipe Pengajuan</th>
                            <th>Nama Pemohon</th>
                            <th>Detail</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th width="160">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permohonans as $p)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $p->tpu ?? '-' }}</td>

                            <td>
                                @if($p->jenis_permohonan === 'perpanjangan')
                                    <span class="badge bg-primary">Perpanjangan Makam Lama</span>
                                @else
                                    <span class="badge bg-success">Pembuatan Makam Baru</span>
                                @endif
                            </td>

                            <td>
                                <strong>{{ $p->user->name ?? 'User tidak ditemukan' }}</strong>
                                <br>
                                <small class="text-muted">{{ $p->user->email ?? '-' }}</small>
                            </td>

                            <td>
                                @if($p->jenis_permohonan === 'perpanjangan')
                                    <strong>{{ $p->makam->kode_makam ?? 'Makam tidak ditemukan' }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        Blok {{ $p->makam->blok ?? '-' }} / Zona {{ $p->makam->zona ?? '-' }}
                                    </small>
                                @else
                                    <strong>{{ $p->nama_jenazah ?? $p->jenazah->nama ?? '-' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $p->nik_jenazah ?? $p->jenazah->nik ?? '-' }}</small>
                                @endif
                            </td>

                            <td>
                                {{ $p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('d-m-Y') : '-' }}
                            </td>

                            <td>
                                @if($p->status == 'menunggu')
                                    <span class="badge bg-warning">Menunggu</span>
                                @elseif($p->status == 'disetujui')
                                    <span class="badge bg-success">Disetujui</span>
                                @else
                                    <span class="badge bg-danger">Ditolak</span>
                                @endif
                            </td>

                            <td>
                                <small title="{{ $p->catatan ?? '-' }}">{{ Str::limit($p->catatan ?? '-', 30) }}</small>
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    {{-- Aksi cepat: hanya tampil selama status masih menunggu review --}}
                                    @if($p->status == 'menunggu')
                                        <form action="{{ route($routePrefix.'.master.permohonan.update', $p->id) }}" method="POST" class="d-flex gap-1">
                                            @csrf
                                            <button type="submit" name="status" value="disetujui"
                                                    class="btn btn-success btn-sm quick-approve-btn"
                                                    title="Setujui permohonan"
                                                    onclick="return confirm('Setujui permohonan dari {{ addslashes($p->user->name ?? 'pemohon ini') }}?')">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button type="submit" name="status" value="ditolak"
                                                    class="btn btn-outline-danger btn-sm quick-reject-btn"
                                                    title="Tolak permohonan"
                                                    onclick="return confirm('Tolak permohonan dari {{ addslashes($p->user->name ?? 'pemohon ini') }}?')">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Menu aksi lainnya --}}
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#detailPermohonan{{ $p->id }}">
                                                    <i class="bi bi-eye me-2"></i>Lihat Detail
                                                </button>
                                            </li>
                                            <li>
                                                <a href="{{ route($routePrefix.'.master.permohonan.edit', $p) }}" class="dropdown-item">
                                                    <i class="bi bi-pencil-square me-2"></i>Edit
                                                </a>
                                            </li>
                                            @if($p->status != 'menunggu')
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route($routePrefix.'.master.permohonan.update', $p->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" name="status" value="{{ $p->status == 'disetujui' ? 'ditolak' : 'disetujui' }}"
                                                                class="dropdown-item {{ $p->status == 'disetujui' ? 'text-danger' : 'text-success' }}"
                                                                onclick="return confirm('Ubah status permohonan ini menjadi {{ $p->status == 'disetujui' ? 'Ditolak' : 'Disetujui' }}?')">
                                                            <i class="bi {{ $p->status == 'disetujui' ? 'bi-x-circle' : 'bi-check-circle' }} me-2"></i>
                                                            Ubah ke {{ $p->status == 'disetujui' ? 'Ditolak' : 'Disetujui' }}
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route($routePrefix.'.master.permohonan.destroy', $p) }}" method="POST" onsubmit="return confirm('Yakin hapus permohonan ini? Tindakan ini tidak dapat dibatalkan.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-trash me-2"></i>Hapus
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="detailPermohonan{{ $p->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Detail Permohonan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">TPU</small>
                                                <strong>{{ $p->tpu ?? '-' }}</strong>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Tipe Pengajuan</small>
                                                <strong>{{ $p->jenis_permohonan === 'perpanjangan' ? 'Perpanjangan Makam Lama' : 'Pembuatan Makam Baru' }}</strong>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Nama Jenazah</small>
                                                <strong>{{ $p->nama_jenazah ?? '-' }}</strong>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">NIK Jenazah</small>
                                                <strong>{{ $p->nik_jenazah ?? '-' }}</strong>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Tempat / Tanggal Lahir</small>
                                                <strong>{{ $p->tempat_lahir ?? '-' }} / {{ $p->tanggal_lahir ? \Carbon\Carbon::parse($p->tanggal_lahir)->format('d-m-Y') : '-' }}</strong>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Tanggal Wafat</small>
                                                <strong>{{ $p->tanggal_wafat ? \Carbon\Carbon::parse($p->tanggal_wafat)->format('d-m-Y') : '-' }}</strong>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Jenis Kelamin</small>
                                                <strong>{{ $p->jenis_kelamin ?? '-' }}</strong>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Agama</small>
                                                <strong>{{ $p->agama ?? '-' }}</strong>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Nama Ahli Waris</small>
                                                <strong>{{ $p->nama_ahli_waris ?? '-' }}</strong>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">No HP Ahli Waris</small>
                                                <strong>{{ $p->no_hp_ahli_waris ?? '-' }}</strong>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Hubungan Keluarga</small>
                                                <strong>{{ $p->hubungan_keluarga ?? '-' }}</strong>
                                            </div>
                                            <div class="col-md-12">
                                                <small class="text-muted d-block">Dokumen</small>
                                                <div class="d-flex flex-wrap gap-2 mt-1">
                                                    @if($p->scan_ktp_ahli_waris)
                                                        <a href="{{ asset('storage/'.$p->scan_ktp_ahli_waris) }}" target="_blank" class="btn btn-outline-primary btn-sm">Scan KTP Ahli Waris</a>
                                                    @endif
                                                    @if($p->scan_kk)
                                                        <a href="{{ asset('storage/'.$p->scan_kk) }}" target="_blank" class="btn btn-outline-primary btn-sm">Scan KK</a>
                                                    @endif
                                                    @if($p->surat_kematian)
                                                        <a href="{{ asset('storage/'.$p->surat_kematian) }}" target="_blank" class="btn btn-outline-primary btn-sm">Surat Kematian</a>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($p->jenis_permohonan === 'perpanjangan')
                                                @php
                                                    $relatedMakam = $p->makam;
                                                    $displayBlokZona = collect([$relatedMakam?->blok, $relatedMakam?->zona])
                                                        ->filter(fn ($value) => filled($value))
                                                        ->implode(' / ');
                                                @endphp
                                                <div class="col-md-12">
                                                    <small class="text-muted d-block">Kode Makam</small>
                                                    <strong>{{ $relatedMakam?->kode_makam ?? '-' }}</strong>
                                                </div>
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block">Blok / Zona</small>
                                                    <strong>{{ $displayBlokZona ?: '-' }}</strong>
                                                </div>
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block">Nomor Makam</small>
                                                    <strong>{{ $relatedMakam?->nomor ?? '-' }}</strong>
                                                </div>
                                                <div class="col-md-12">
                                                    <small class="text-muted d-block">Keterangan Makam</small>
                                                    <strong>{{ $relatedMakam?->keterangan ?? '-' }}</strong>
                                                </div>
                                            @endif
                                            <div class="col-md-12">
                                                <small class="text-muted d-block">Catatan</small>
                                                <strong>{{ $p->catatan ?? '-' }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Tidak ada data permohonan yang cocok dengan filter ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                @if(($permohonans ?? null) instanceof \Illuminate\Contracts\Pagination\Paginator && $permohonans->hasPages())
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top">
                        <small class="text-muted">
                            Menampilkan {{ $permohonans->firstItem() }} - {{ $permohonans->lastItem() }} dari {{ $permohonans->total() }} data
                        </small>
                        {{ $permohonans->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection