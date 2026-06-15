@extends('admin.layouts.app')

@section('title', 'Laporan Pemakaman')

@section('content')
@php
    $routePrefix = request()->routeIs('petugas.*') ? 'petugas' : 'admin';
    $canEditData = auth()->user()?->isAdmin() || auth()->user()?->isPetugas();
    $isAdmin = auth()->user()?->isAdmin();
@endphp

<div class="container-fluid pt-2 pb-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Laporan Pemakaman</h4>
            <p class="text-muted mb-0">Rekap data pemakaman berdasarkan periode waktu</p>
        </div>
        <div>
            <span class="badge px-3 py-2" style="background-color: #1E3E62; color: white;">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($isAdmin && ! empty($wordRoute))
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="{{ $printRoute }}" target="_blank" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-printer"></i> Cetak PDF
            </a>
            <a href="{{ $wordRoute }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-file-earmark-word"></i> Export Word
            </a>
            <a href="{{ $exportExcelRoute }}" class="btn btn-success btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
    @endif

    @if($isPetugasReport ?? false || $isKepalaReport ?? false)
        @php
            $reportTitle = ($isKepalaReport ?? false) ? 'Laporan Kepala TPU' : 'Laporan Petugas TPU';
            $reportDescription = ($isKepalaReport ?? false)
                ? 'Gabungan data pemakaman, permohonan, dan jenazah untuk ' . auth()->user()->tpu
                : 'Gabungan data permohonan dan data jenazah untuk ' . auth()->user()->tpu;
        @endphp
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">{{ $reportTitle }}</h4>
                <p class="text-muted mb-0">{{ $reportDescription }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ $printRoute }}" target="_blank" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-printer"></i> Cetak PDF
                </a>
                @if(! empty($wordRoute))
                    <a href="{{ $wordRoute }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-file-earmark-word"></i> Export Word
                    </a>
                @endif
                <a href="{{ $exportExcelRoute }}" class="btn btn-success btn-sm">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </a>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ request()->url() }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Jenis Laporan</label>
                            <select name="filter" class="form-select">
                                <option value="harian" @selected(($filter ?? 'harian') === 'harian')>Harian</option>
                                <option value="mingguan" @selected(($filter ?? 'harian') === 'mingguan')>Mingguan</option>
                                <option value="bulanan" @selected(($filter ?? 'harian') === 'bulanan')>Bulanan</option>
                                <option value="tahunan" @selected(($filter ?? 'harian') === 'tahunan')>Tahunan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="start" value="{{ $start ?? request('start') }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" name="end" value="{{ $end ?? request('end') }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <button class="btn w-100" style="background-color:#1E3E62;color:white;">
                                <i class="bi bi-filter"></i> Tampilkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($isKepalaReport ?? false)
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <p class="text-muted mb-1">Total Pemakaman</p>
                            <h4 class="fw-bold">{{ $totalPemakaman ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <p class="text-muted mb-1">Permohonan Masuk</p>
                            <h4 class="fw-bold">{{ $totalPermohonan ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <p class="text-muted mb-1">Laki-laki</p>
                            <h4 class="fw-bold">{{ $laki ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <p class="text-muted mb-1">Perempuan</p>
                            <h4 class="fw-bold">{{ $perempuan ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <p class="text-muted mb-1">Total Data</p>
                            <h4 class="fw-bold">{{ $total ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <p class="text-muted mb-1">Menunggu</p>
                            <h4 class="fw-bold">{{ $permohonanMenunggu ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <p class="text-muted mb-1">Disetujui</p>
                            <h4 class="fw-bold text-success">{{ $permohonanDisetujui ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <p class="text-muted mb-1">Ditolak</p>
                            <h4 class="fw-bold text-danger">{{ $permohonanDitolak ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <p class="text-muted mb-1">Total Data</p>
                            <h4 class="fw-bold">{{ $total ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <p class="text-muted mb-1">Permohonan Masuk</p>
                            <h4 class="fw-bold">{{ $totalPermohonan ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <p class="text-muted mb-1">Data Jenazah</p>
                            <h4 class="fw-bold">{{ $totalJenazah ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <p class="text-muted mb-1">Makam Terhubung</p>
                            <h4 class="fw-bold">{{ $totalMakamTerhubung ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-0 py-3 px-4">
                <h6 class="fw-bold mb-0">Data Pemakaman Gabungan</h6>
                <small class="text-muted">Memuat data permohonan dan data jenazah yang masuk untuk TPU ini</small>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Sumber</th>
                                <th>Nama Jenazah</th>
                                <th>NIK</th>
                                <th>Jenis Kelamin</th>
                                <th>Tanggal Input</th>
                                <th>Tanggal Wafat</th>
                                <th>Kode Makam</th>
                                <th>Blok</th>
                                <th>Zona</th>
                                <th>Nomor</th>
                                <th>Status Permohonan</th>
                                <th>Status Makam</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reportRows as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="badge {{ $row['source'] === 'permohonan' ? 'bg-primary' : 'bg-success' }}">
                                            {{ $row['source_label'] }}
                                        </span>
                                    </td>
                                    <td>{{ $row['nama'] }}</td>
                                    <td>{{ $row['nik'] }}</td>
                                    <td>{{ $row['jenis_kelamin'] }}</td>
                                    <td>{{ $row['tanggal_input_label'] }}</td>
                                    <td>{{ $row['tanggal_wafat_label'] }}</td>
                                    <td>{{ $row['kode_makam'] }}</td>
                                    <td>{{ $row['blok'] }}</td>
                                    <td>{{ $row['zona'] }}</td>
                                    <td>{{ $row['nomor_makam'] }}</td>
                                    <td>
                                        @if($row['source'] === 'permohonan')
                                            <span class="badge {{ $row['status_permohonan'] === 'disetujui' ? 'bg-success' : ($row['status_permohonan'] === 'ditolak' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                                {{ ucfirst($row['status_permohonan'] ?? '-') }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php $statusMakam = $row['status_makam'] ?? null; @endphp
                                        <span class="badge {{ $statusMakam === 'kosong' ? 'bg-secondary' : ($statusMakam ? 'bg-success' : 'bg-warning text-dark') }}">
                                            {{ $statusMakam ? ucfirst($statusMakam) : 'Belum Ada' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $row['source'] === 'permohonan' ? ($row['nama_ahli_waris'] !== '-' ? 'Ahli waris: '.$row['nama_ahli_waris'].' | ' : '') : '' }}{{ $row['catatan'] }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center text-muted">Tidak ada data laporan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
    {{-- Filter --}}
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ request()->url() }}">
                <div class="row g-3 align-items-end">
                    @if($isAdmin)
                        <div class="col-md-3">
                            <label class="form-label">TPU</label>
                            <select name="tpu" class="form-select">
                                <option value="">Semua TPU</option>
                                @foreach($tpuOptions ?? [] as $tpu)
                                    <option value="{{ $tpu }}" @selected(($selectedTpu ?? '') === $tpu)>{{ $tpu }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="{{ $isAdmin ? 'col-md-3' : 'col-md-3' }}">
                        <label class="form-label">Jenis Laporan</label>
                        <select name="filter" class="form-select">
                            <option value="harian" {{ request('filter')=='harian'?'selected':'' }}>Harian</option>
                            <option value="mingguan" {{ request('filter')=='mingguan'?'selected':'' }}>Mingguan</option>
                            <option value="bulanan" {{ request('filter')=='bulanan'?'selected':'' }}>Bulanan</option>
                            <option value="tahunan" {{ request('filter')=='tahunan'?'selected':'' }}>Tahunan</option>
                        </select>
                    </div>

                    <div class="{{ $isAdmin ? 'col-md-2' : 'col-md-3' }}">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start" value="{{ request('start') }}" class="form-control">
                    </div>

                    <div class="{{ $isAdmin ? 'col-md-2' : 'col-md-3' }}">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="end" value="{{ request('end') }}" class="form-control">
                    </div>

                    <div class="{{ $isAdmin ? 'col-md-2' : 'col-md-3' }}">
                        <button class="btn w-100" style="background-color:#1E3E62;color:white;">
                            <i class="bi bi-filter"></i> Tampilkan
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="row g-4 mb-4">

        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Pemakaman</p>
                    <h4 class="fw-bold">{{ $total ?? 0 }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <p class="text-muted mb-1">Laki-laki</p>
                    <h4 class="fw-bold">{{ $laki ?? 0 }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <p class="text-muted mb-1">Perempuan</p>
                    <h4 class="fw-bold">{{ $perempuan ?? 0 }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <p class="text-muted mb-1">Makam Terhubung</p>
                    <h4 class="fw-bold">{{ $totalMakamTerisi ?? 0 }}</h4>
                </div>
            </div>
        </div>

    </div>

    {{-- Tabel --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h6 class="fw-bold mb-0">Data Pemakaman</h6>
            <small class="text-muted">Hasil laporan berdasarkan filter</small>
        </div>

        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Sumber</th>
                            <th>Nama Jenazah</th>
                            <th>NIK</th>
                            <th>Jenis Kelamin</th>
                            <th>Tanggal Input</th>
                            <th>Tanggal Wafat</th>
                            <th>Kode Makam</th>
                            <th>Blok</th>
                            <th>Zona</th>
                            <th>Nomor</th>
                            <th>Status Permohonan</th>
                            <th>Status Makam</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportRows ?? [] as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="badge {{ $row['source'] === 'permohonan' ? 'bg-primary' : 'bg-success' }}">
                                    {{ $row['source_label'] }}
                                </span>
                            </td>
                            <td>{{ $row['nama'] }}</td>
                            <td>{{ $row['nik'] }}</td>
                            <td>{{ $row['jenis_kelamin'] }}</td>
                            <td>{{ $row['tanggal_input_label'] }}</td>
                            <td>{{ $row['tanggal_wafat_label'] }}</td>
                            <td>{{ $row['kode_makam'] }}</td>
                            <td>{{ $row['blok'] }}</td>
                            <td>{{ $row['zona'] }}</td>
                            <td>{{ $row['nomor_makam'] }}</td>
                            <td>
                                @if($row['source'] === 'permohonan')
                                    <span class="badge {{ $row['status_permohonan'] === 'disetujui' ? 'bg-success' : ($row['status_permohonan'] === 'ditolak' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                        {{ ucfirst($row['status_permohonan'] ?? '-') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php $statusMakam = $row['status_makam'] ?? null; @endphp
                                <span class="badge {{ $statusMakam === 'kosong' ? 'bg-secondary' : ($statusMakam ? 'bg-success' : 'bg-warning text-dark') }}">
                                    {{ $statusMakam ? ucfirst($statusMakam) : 'Belum Ada' }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $row['source'] === 'permohonan' ? ($row['nama_ahli_waris'] !== '-' ? 'Ahli waris: '.$row['nama_ahli_waris'].' | ' : '') : '' }}{{ $row['catatan'] }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="14" class="text-center text-muted">
                                Tidak ada data laporan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
    @endif
@endsection
