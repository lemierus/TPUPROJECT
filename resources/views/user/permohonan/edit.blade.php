@extends('admin.layouts.app')

@section('title', 'Edit Permohonan')

@section('content')
@php
    $isPerpanjangan = false;
@endphp

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Edit Permohonan</h4>
            <p class="text-muted mb-0">TPU tujuan: {{ $permohonan->tpu }}</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body px-4 py-4">
            <form action="{{ route('user.permohonan.update', $permohonan) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <input type="hidden" name="tpu" value="{{ $permohonan->tpu }}">
                <input type="hidden" name="jenis_permohonan" value="makam_baru">

                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <h6 class="fw-bold mb-0">Data Jenazah</h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">NIK Jenazah</label>
                        <input type="text" name="nik_jenazah" class="form-control" value="{{ old('nik_jenazah', $permohonan->nik_jenazah) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nama Jenazah</label>
                        <input type="text" name="nama_jenazah" class="form-control" value="{{ old('nama_jenazah', $permohonan->nama_jenazah) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $permohonan->tempat_lahir) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select">
                            <option value="">Pilih</option>
                            <option value="Laki-laki" @selected(old('jenis_kelamin', $permohonan->jenis_kelamin) === 'Laki-laki')>Laki-laki</option>
                            <option value="Perempuan" @selected(old('jenis_kelamin', $permohonan->jenis_kelamin) === 'Perempuan')>Perempuan</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Agama</label>
                        <input type="text" name="agama" class="form-control" value="{{ old('agama', $permohonan->agama) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', $permohonan->tanggal_lahir) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Wafat</label>
                        <input type="date" name="tanggal_wafat" class="form-control" value="{{ old('tanggal_wafat', $permohonan->tanggal_wafat) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $permohonan->alamat ?? '') }}</textarea>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <h6 class="fw-bold mb-0">Data Ahli Waris</h6>
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

                    <div class="col-12">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea name="catatan" class="form-control" rows="3">{{ old('catatan', $permohonan->catatan) }}</textarea>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <h6 class="fw-bold mb-0">Upload Dokumen</h6>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Scan KTP Ahli Waris</label>
                        <input type="file" name="scan_ktp_ahli_waris" class="form-control" accept=".jpg,.jpeg,.png,.pdf"
                            {{ !$permohonan->scan_ktp_ahli_waris ? 'required' : '' }}>
                        @if($permohonan->scan_ktp_ahli_waris)
                            <small class="text-muted">Saat ini: <a href="{{ asset('storage/' . $permohonan->scan_ktp_ahli_waris) }}" target="_blank">Lihat file</a></small>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Scan KK</label>
                        <input type="file" name="scan_kk" class="form-control" accept=".jpg,.jpeg,.png,.pdf"
                            {{ !$permohonan->scan_kk ? 'required' : '' }}>
                        @if($permohonan->scan_kk)
                            <small class="text-muted">Saat ini: <a href="{{ asset('storage/' . $permohonan->scan_kk) }}" target="_blank">Lihat file</a></small>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Surat Kematian</label>
                        <input type="file" name="surat_kematian" class="form-control" accept=".jpg,.jpeg,.png,.pdf"
                            {{ !$permohonan->surat_kematian ? 'required' : '' }}>
                        @if($permohonan->surat_kematian)
                            <small class="text-muted">Saat ini: <a href="{{ asset('storage/' . $permohonan->surat_kematian) }}" target="_blank">Lihat file</a></small>
                        @endif
                    </div>
                </div>

                <div class="mt-3">
                    <strong>Petugas Penanggungjawab: </strong>
                    @if($assignedPetugas)
                        {{ $assignedPetugas->name }} ({{ $assignedPetugas->email }})
                    @else
                        <span class="text-muted">Belum ada petugas ditugaskan</span>
                    @endif
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn" style="background-color:#1E3E62;color:white;">
                        <i class="bi bi-send"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection