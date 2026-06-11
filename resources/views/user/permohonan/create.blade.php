@extends('admin.layouts.app')

@section('title', 'Ajukan Permohonan')

@section('content')
@php
    $jenis = 'makam_baru';
@endphp

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Permohonan Ahli Waris</h4>
            <p class="text-muted mb-0">TPU tujuan: {{ $tpu }}</p>
            @if(isset($assignedPetugas) && $assignedPetugas)
                <p class="text-muted mb-0">Petugas TPU: <strong>{{ $assignedPetugas->name }}</strong> ({{ $assignedPetugas->email }})</p>
            @else
                <p class="text-muted mb-0">Petugas TPU: <strong>Belum ada petugas terdaftar untuk TPU ini</strong></p>
            @endif
        </div>
        <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="alert alert-info border-2 border-dark shadow-sm mb-4">
        <i class="bi bi-info-circle-fill me-2"></i>
        <strong>Informasi:</strong> Anda memiliki waktu 24 jam untuk menyelesaikan dan mengirim form permohonan ini. Pastikan semua data sudah diisi dengan lengkap dan benar sebelum mengirim.
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body px-4 py-4">
            <form action="{{ route('user.permohonan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="tpu" value="{{ $tpu }}">

                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <h6 class="fw-bold mb-2">Jenis Permohonan</h6>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jenis_permohonan" id="jenis_makam_baru" value="makam_baru" @checked($jenis === 'makam_baru') onchange="toggleJenisPermohonan('makam_baru')" checked>
                            <label class="form-check-label" for="jenis_makam_baru">
                                Permohonan Pembuatan Makam Baru
                            </label>
                        </div>
                    </div>
                </div>

                <div id="section-makam-baru" class="jenis-section">
                    <div class="row g-3 mt-3">
                        <div class="col-12">
                            <h6 class="fw-bold mb-0">Data Jenazah</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">NIK Jenazah <span class="text-danger">*</span></label>
                            <input type="text" name="nik_jenazah" class="form-control" value="{{ old('nik_jenazah') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nama Jenazah <span class="text-danger">*</span></label>
                            <input type="text" name="nama_jenazah" class="form-control" value="{{ old('nama_jenazah') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="">Pilih</option>
                                <option value="Laki-laki" @selected(old('jenis_kelamin') === 'Laki-laki')>Laki-laki</option>
                                <option value="Perempuan" @selected(old('jenis_kelamin') === 'Perempuan')>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Agama</label>
                            <input type="text" name="agama" class="form-control" value="{{ old('agama') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tanggal Wafat</label>
                            <input type="date" name="tanggal_wafat" class="form-control" value="{{ old('tanggal_wafat') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3">{{ old('alamat') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <h6 class="fw-bold mb-0">Data Ahli Waris</h6>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nama Ahli Waris</label>
                        <input type="text" name="nama_ahli_waris" class="form-control" value="{{ old('nama_ahli_waris', auth()->user()->name) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">No HP Ahli Waris</label>
                        <input type="text" name="no_hp_ahli_waris" class="form-control" value="{{ old('no_hp_ahli_waris') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Hubungan Keluarga</label>
                        <input type="text" name="hubungan_keluarga" class="form-control" value="{{ old('hubungan_keluarga') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea name="catatan" class="form-control" rows="3">{{ old('catatan') }}</textarea>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <h6 class="fw-bold mb-0">Upload Dokumen</h6>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Scan KTP Ahli Waris</label>
                        <input type="file" name="scan_ktp_ahli_waris" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Scan KK</label>
                        <input type="file" name="scan_kk" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Surat Kematian</label>
                        <input type="file" name="surat_kematian" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn" style="background-color:#1E3E62;color:white;">
                        <i class="bi bi-send"></i> Kirim Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
