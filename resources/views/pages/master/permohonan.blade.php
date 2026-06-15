@extends('admin.layouts.app')

@section('title', 'Permohonan')

@section('content')
@php
    $routePrefix = request()->routeIs('petugas.*') ? 'petugas' : 'admin';
    $isAdmin = auth()->user()?->isAdmin();
@endphp

<div class="container-fluid pt-2 pb-4">

    {{-- Header (DISAMAKAN DENGAN DASHBOARD) --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Data Permohonan</h4>
            <p class="text-muted mb-0">Daftar permohonan pengajuan makam dari masyarakat</p>
        </div>
        <div>
            <a href="{{ route($routePrefix.'.master.permohonan.create', request()->only('tpu')) }}" class="btn btn-sm me-2" style="background-color:#1E3E62;color:white;">
                <i class="bi bi-plus-circle"></i> Tambah Permohonan
            </a>
            <span class="badge px-3 py-2" style="background-color: #1E3E62; color: white;">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($isAdmin)
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route($routePrefix.'.master.permohonan') }}">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <select name="tpu" class="form-select">
                                <option value="">Semua TPU</option>
                                @foreach($tpuOptions ?? [] as $tpu)
                                    <option value="{{ $tpu }}" @selected(($selectedTpu ?? '') === $tpu)>{{ $tpu }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn w-100" style="background-color:#1E3E62;color:white;">
                                <i class="bi bi-filter"></i> Tampilkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- TABEL --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h6 class="fw-bold mb-0">Data Permohonan</h6>
            <small class="text-muted">Daftar permohonan yang masuk ke sistem</small>
        </div>

        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
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
                            <th width="360">Aksi</th>
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

                            <td>{{ $p->catatan ?? '-' }}</td>

                            <td>
                                <div class="d-flex gap-2">
                                    <form action="{{ route($routePrefix.'.master.permohonan.update', $p->id) }}" method="POST" class="d-flex gap-2">
                                        @csrf
                                        <button name="status" value="disetujui" class="btn btn-success btn-sm w-100">
                                            <i class="bi bi-check-circle"></i>
                                        </button>

                                        <button name="status" value="ditolak" class="btn btn-danger btn-sm w-100">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#detailPermohonan{{ $p->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <a href="{{ route($routePrefix.'.master.permohonan.edit', $p) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <form action="{{ route($routePrefix.'.master.permohonan.destroy', $p) }}" method="POST" onsubmit="return confirm('Yakin hapus permohonan?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
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
                                            @if($p->bukti_pembayaran_retribusi)
                                                <div class="col-md-12">
                                                    <small class="text-muted d-block">Bukti Pembayaran Retribusi</small>
                                                    <a href="{{ asset('storage/'.$p->bukti_pembayaran_retribusi) }}" target="_blank" class="btn btn-outline-primary btn-sm mt-1">
                                                        Lihat Bukti Retribusi
                                                    </a>
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
                            <td colspan="9" class="text-center text-muted">
                                Tidak ada data permohonan
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
