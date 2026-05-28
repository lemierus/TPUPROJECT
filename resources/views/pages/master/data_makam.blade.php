@extends('admin.layouts.app')

@section('title', 'Data Makam')

@section('content')
@php
    $routePrefix = request()->routeIs('petugas.*') ? 'petugas' : 'admin';
@endphp

<div class="container-fluid pt-2 pb-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Data Makam</h4>
            <p class="text-muted mb-0">Daftar data makam dalam sistem</p>
        </div>
        <div>
            <a href="{{ route($routePrefix.'.data-makam.create') }}" class="btn btn-sm me-2" style="background-color:#1E3E62;color:white;">
                <i class="bi bi-plus-circle"></i> Tambah Makam
            </a>
            <span class="badge px-3 py-2" style="background-color: #1E3E62; color: white;">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- SEARCH --}}
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route($routePrefix.'.data-makam') }}">
                <div class="row g-2">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control"
                               placeholder="Cari kode, blok, zona..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
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
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>TPU</th>
                            <th>Kode Makam</th>
                            <th>Blok</th>
                            <th>Zona</th>
                            <th>Nomor</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($makams as $makam)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $makam->tpu ?? '-' }}</td>
                            <td>{{ $makam->kode_makam }}</td>
                            <td>{{ $makam->blok ?? '-' }}</td>
                            <td>{{ $makam->zona ?? '-' }}</td>
                            <td>{{ $makam->nomor ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $makam->status === 'kosong' ? 'bg-secondary' : 'bg-success' }}">
                                    {{ ucfirst($makam->status) }}
                                </span>
                            </td>
                            <td>{{ $makam->keterangan ?? '-' }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route($routePrefix.'.data-makam.edit', $makam) }}" class="btn btn-warning btn-sm d-flex align-items-center gap-1">
                                        <i class="bi bi-pencil-square"></i>
                                        Edit
                                    </a>

                                    <form action="{{ route($routePrefix.'.data-makam.destroy', $makam) }}" method="POST" onsubmit="return confirm('Yakin hapus data?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm d-flex align-items-center gap-1">
                                            <i class="bi bi-trash"></i>
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                Data tidak ditemukan
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

@push('scripts')
@endpush
