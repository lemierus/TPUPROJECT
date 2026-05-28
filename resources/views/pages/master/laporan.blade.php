@extends('admin.layouts.app')

@section('title', 'Laporan Pemakaman')

@section('content')
@php
    $routePrefix = request()->routeIs('petugas.*') ? 'petugas' : 'admin';
    $canEditData = auth()->user()?->isAdmin() || auth()->user()?->isPetugas();
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

    {{-- Filter --}}
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ request()->url() }}">
                <div class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label class="form-label">Jenis Laporan</label>
                        <select name="filter" class="form-select">
                            <option value="harian" {{ request('filter')=='harian'?'selected':'' }}>Harian</option>
                            <option value="mingguan" {{ request('filter')=='mingguan'?'selected':'' }}>Mingguan</option>
                            <option value="bulanan" {{ request('filter')=='bulanan'?'selected':'' }}>Bulanan</option>
                            <option value="tahunan" {{ request('filter')=='tahunan'?'selected':'' }}>Tahunan</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start" value="{{ request('start') }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="end" value="{{ request('end') }}" class="form-control">
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
                            <th>Nama Jenazah</th>
                            <th>NIK</th>
                            <th>Jenis Kelamin</th>
                            <th>Tanggal Wafat</th>
                            <th>Kode Makam</th>
                            <th>Blok</th>
                            <th>Zona</th>
                            <th>Nomor</th>
                            <th>Status Makam</th>
                            @if($canEditData)
                                <th width="120">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $d)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $d->nama }}</td>
                            <td>{{ $d->nik }}</td>
                            <td>{{ $d->jenis_kelamin }}</td>
                            <td>{{ \Carbon\Carbon::parse($d->tanggal_wafat)->format('d-m-Y') }}</td>
                            <td>{{ $d->makam->kode_makam ?? '-' }}</td>
                            <td>{{ $d->makam->blok ?? '-' }}</td>
                            <td>{{ $d->makam->zona ?? '-' }}</td>
                            <td>{{ $d->makam->nomor ?? '-' }}</td>
                            <td>
                                @if($d->makam)
                                    <span class="badge {{ $d->makam->status === 'kosong' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($d->makam->status) }}
                                    </span>
                                @else
                                    <span class="badge bg-warning">Belum Ada</span>
                                @endif
                            </td>
                            @if($canEditData)
                                <td>
                                    <a href="{{ route($routePrefix.'.data-jenazah.edit', $d->id) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $canEditData ? 10 : 9 }}" class="text-center text-muted">
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
@endsection
