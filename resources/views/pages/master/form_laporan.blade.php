@extends('admin.layouts.app')

@php
    $isEdit = $laporan->exists;
    $routePrefix = request()->routeIs('petugas.*') ? 'petugas' : 'admin';
@endphp

@section('title', $isEdit ? 'Edit Laporan' : 'Tambah Laporan')

@section('content')
<div class="container-fluid pt-2 pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">{{ $isEdit ? 'Edit Laporan' : 'Tambah Laporan' }}</h4>
            <p class="text-muted mb-0">Kelola data laporan pemakaman.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body px-4 py-4">
            <form action="{{ $isEdit ? route($routePrefix.'.master.laporan.update', $laporan) : route($routePrefix.'.master.laporan.store') }}" method="POST">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Jenazah</label>
                        <input type="text" name="nama_jenazah" class="form-control @error('nama_jenazah') is-invalid @enderror" value="{{ old('nama_jenazah', $laporan->nama_jenazah) }}" required>
                        @error('nama_jenazah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                            <option value="L" @selected(old('jenis_kelamin', $laporan->jenis_kelamin) === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('jenis_kelamin', $laporan->jenis_kelamin) === 'P')>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tanggal Wafat</label>
                        <input type="date" name="tanggal_wafat" class="form-control @error('tanggal_wafat') is-invalid @enderror" value="{{ old('tanggal_wafat', $laporan->tanggal_wafat) }}" required>
                        @error('tanggal_wafat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Periode</label>
                        <select name="periode" class="form-select @error('periode') is-invalid @enderror" required>
                            <option value="harian" @selected(old('periode', $laporan->periode ?: 'harian') === 'harian')>Harian</option>
                            <option value="mingguan" @selected(old('periode', $laporan->periode) === 'mingguan')>Mingguan</option>
                            <option value="bulanan" @selected(old('periode', $laporan->periode) === 'bulanan')>Bulanan</option>
                            <option value="tahunan" @selected(old('periode', $laporan->periode) === 'tahunan')>Tahunan</option>
                        </select>
                        @error('periode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Makam</label>
                        <input type="text" name="makam" class="form-control @error('makam') is-invalid @enderror" value="{{ old('makam', $laporan->makam) }}">
                        @error('makam')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Blok</label>
                        <input type="text" name="blok" class="form-control @error('blok') is-invalid @enderror" value="{{ old('blok', $laporan->blok) }}">
                        @error('blok')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Zona</label>
                        <input type="text" name="zona" class="form-control @error('zona') is-invalid @enderror" value="{{ old('zona', $laporan->zona) }}">
                        @error('zona')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route($routePrefix.'.master.laporan') }}" class="btn btn-outline-secondary">Kembali</a>
                    <button type="submit" class="btn" style="background-color:#1E3E62;color:white;">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
