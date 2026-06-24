@extends('admin.layouts.app')

@php
    $isEdit = $makam->exists;
    $routePrefix = request()->routeIs('petugas.*')
        ? 'petugas'
        : (request()->routeIs('kepala.*') ? 'kepala' : 'admin');
    $isPetugas = auth()->user()?->isPetugas();
@endphp

@section('title', $isEdit ? 'Edit Data Makam' : 'Tambah Data Makam')

@section('content')
<div class="container-fluid pt-2 pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">{{ $isEdit ? 'Edit Data Makam' : 'Tambah Data Makam' }}</h4>
            <p class="text-muted mb-0">Kelola lokasi makam TPU.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body px-4 py-4">
            <form action="{{ $isEdit ? route($routePrefix.'.data-makam.update', $makam) : route($routePrefix.'.data-makam.store') }}" method="POST">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">TPU</label>
                        <select name="tpu" class="form-select @error('tpu') is-invalid @enderror" required {{ $isPetugas ? 'disabled' : '' }}>
                            <option value="">Pilih TPU</option>
                            @foreach($tpuOptions ?? ['TPU Tunggul Hitam', 'TPU Bungus Teluk Kabung', 'TPU Air Dingin'] as $tpu)
                                <option value="{{ $tpu }}" @selected(old('tpu', $makam->tpu ?? $selectedTpu ?? auth()->user()->tpu) === $tpu)>{{ $tpu }}</option>
                            @endforeach
                        </select>
                        @if($isPetugas)
                            <input type="hidden" name="tpu" value="{{ auth()->user()->tpu }}">
                        @endif
                        @error('tpu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Kode Makam</label>
                        <input type="text" name="kode_makam" class="form-control @error('kode_makam') is-invalid @enderror" value="{{ old('kode_makam', $makam->kode_makam) }}" required>
                        @error('kode_makam')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="kosong" @selected(old('status', $makam->status ?: 'kosong') === 'kosong')>Kosong</option>
                            <option value="terisi" @selected(old('status', $makam->status) === 'terisi')>Terisi</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Blok</label>
                        <input type="text" name="blok" class="form-control @error('blok') is-invalid @enderror" value="{{ old('blok', $makam->blok) }}">
                        @error('blok')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Zona</label>
                        <input type="text" name="zona" class="form-control @error('zona') is-invalid @enderror" value="{{ old('zona', $makam->zona) }}">
                        @error('zona')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nomor</label>
                        <input type="text" name="nomor" class="form-control @error('nomor') is-invalid @enderror" value="{{ old('nomor', $makam->nomor) }}">
                        @error('nomor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3">{{ old('keterangan', $makam->keterangan) }}</textarea>
                        @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn" style="background-color:#1E3E62;color:white;">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
