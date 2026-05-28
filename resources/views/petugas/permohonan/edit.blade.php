@extends('admin.layouts.app')

@section('title', 'Edit Permohonan')

@section('content')
@php
    $isPerpanjangan = $permohonan->jenis_permohonan === 'perpanjangan';
@endphp

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Edit Permohonan</h4>
            <p class="text-muted mb-0">ID: #{{ $permohonan->id }} | TPU: {{ $permohonan->tpu }}</p>
        </div>
        <a href="{{ route('petugas.permohonan.show', $permohonan) }}" class="btn btn-outline-secondary btn-sm">Batal</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body px-4 py-4">
            <form action="{{ route('petugas.permohonan.update', $permohonan) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Informasi Permohonan -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold mb-3">Informasi Permohonan</h6>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jenis Permohonan</label>
                        <input type="text" class="form-control" readonly value="{{ $permohonan->jenis_permohonan === 'perpanjangan' ? 'Perpanjangan Makam' : 'Makam Baru' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">TPU Tujuan</label>
                        <input type="text" class="form-control" readonly value="{{ $permohonan->tpu }}">
                    </div>
                </div>

                <hr class="mb-4">

                <!-- Data Jenazah / Makam -->
                @if($isPerpanjangan)
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3">Data Makam</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kode Makam</label>
                            <input type="text" class="form-control" readonly value="{{ $permohonan->makam?->kode_makam ?? '-' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor Makam</label>
                            <input type="text" name="no_makam" class="form-control" value="{{ old('no_makam', $permohonan->no_makam) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Blok / Zona Makam</label>
                            <input type="text" name="blok_zona_makam" class="form-control" value="{{ old('blok_zona_makam', $permohonan->blok_zona_makam) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahun Pemakaman</label>
                            <input type="text" class="form-control" readonly value="{{ $permohonan->tahun_pemakaman ?? '-' }}">
                        </div>
                    </div>
                @else
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold mb-3">Data Jenazah</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Jenazah</label>
                            <input type="text" name="nama_jenazah" class="form-control" value="{{ old('nama_jenazah', $permohonan->nama_jenazah) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIK Jenazah</label>
                            <input type="text" name="nik_jenazah" class="form-control" value="{{ old('nik_jenazah', $permohonan->nik_jenazah) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $permohonan->tempat_lahir) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', $permohonan->tanggal_lahir) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Wafat</label>
                            <input type="date" name="tanggal_wafat" class="form-control" value="{{ old('tanggal_wafat', $permohonan->tanggal_wafat) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="">Pilih</option>
                                <option value="Laki-laki" @selected(old('jenis_kelamin', $permohonan->jenis_kelamin) === 'Laki-laki')>Laki-laki</option>
                                <option value="Perempuan" @selected(old('jenis_kelamin', $permohonan->jenis_kelamin) === 'Perempuan')>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Agama</label>
                            <input type="text" name="agama" class="form-control" value="{{ old('agama', $permohonan->agama) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat Jenazah</label>
                            <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $permohonan->alamat) }}</textarea>
                        </div>
                    </div>
                @endif

                <hr class="mb-4">

                <!-- Data Ahli Waris -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold mb-3">Data Ahli Waris</h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nama Ahli Waris</label>
                        <input type="text" name="nama_ahli_waris" class="form-control" value="{{ old('nama_ahli_waris', $permohonan->nama_ahli_waris) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">No HP Ahli Waris</label>
                        <input type="text" name="no_hp_ahli_waris" class="form-control" value="{{ old('no_hp_ahli_waris', $permohonan->no_hp_ahli_waris) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hubungan Keluarga</label>
                        <input type="text" name="hubungan_keluarga" class="form-control" value="{{ old('hubungan_keluarga', $permohonan->hubungan_keluarga) }}">
                    </div>
                </div>

                <hr class="mb-4">

                <!-- Catatan -->
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label fw-bold">Catatan Petugas</label>
                        <textarea name="catatan" class="form-control" rows="3">{{ old('catatan', $permohonan->catatan) }}</textarea>
                        <small class="text-muted">Catatan untuk ahli waris atau internal petugas</small>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('petugas.permohonan.show', $permohonan) }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn" style="background-color: #1E3E62; color: white;">
                        <i class="bi bi-check-circle"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
