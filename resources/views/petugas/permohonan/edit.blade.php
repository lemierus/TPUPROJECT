@extends('admin.layouts.app')

@section('title', 'Edit Permohonan Petugas')

@php
    $isPerpanjangan = $permohonan->jenis_permohonan === 'perpanjangan';
    $linkedJenazah = $permohonan->jenazah;
    $linkedMakam = $permohonan->makam ?? $linkedJenazah?->makam;
    $renewalDueAt = $permohonan->renewalDueAt();
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Edit Permohonan Petugas</h4>
            <p class="text-muted mb-0">ID: #{{ $permohonan->id }} | TPU: {{ $permohonan->tpu }}</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body px-4 py-4">
            <form action="{{ route('petugas.permohonan.update', $permohonan) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <input type="hidden" name="tpu" value="{{ $permohonan->tpu }}">
                <input type="hidden" name="jenis_permohonan" value="{{ $permohonan->jenis_permohonan }}">

                <div class="row g-3">
                    <div class="col-12">
                        <h6 class="fw-bold mb-0">Data Jenazah</h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">NIK Jenazah <span class="text-danger">*</span></label>
                        <input type="text" name="nik_jenazah" class="form-control @error('nik_jenazah') is-invalid @enderror" value="{{ old('nik_jenazah', $permohonan->nik_jenazah ?? $linkedJenazah?->nik) }}">
                        @error('nik_jenazah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nama Jenazah <span class="text-danger">*</span></label>
                        <input type="text" name="nama_jenazah" class="form-control @error('nama_jenazah') is-invalid @enderror" value="{{ old('nama_jenazah', $permohonan->nama_jenazah ?? $linkedJenazah?->nama) }}">
                        @error('nama_jenazah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror" value="{{ old('tempat_lahir', $permohonan->tempat_lahir ?? $linkedJenazah?->tempat_lahir) }}">
                        @error('tempat_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
                            <option value="">Pilih</option>
                            <option value="Laki-laki" @selected(old('jenis_kelamin', $permohonan->jenis_kelamin ?? $linkedJenazah?->jenis_kelamin) === 'Laki-laki')>Laki-laki</option>
                            <option value="Perempuan" @selected(old('jenis_kelamin', $permohonan->jenis_kelamin ?? $linkedJenazah?->jenis_kelamin) === 'Perempuan')>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Agama</label>
                        <input type="text" name="agama" class="form-control @error('agama') is-invalid @enderror" value="{{ old('agama', $permohonan->agama ?? $linkedJenazah?->agama) }}">
                        @error('agama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir', $permohonan->tanggal_lahir ? \Carbon\Carbon::parse($permohonan->tanggal_lahir)->format('Y-m-d') : ($linkedJenazah?->tanggal_lahir ? \Carbon\Carbon::parse($linkedJenazah->tanggal_lahir)->format('Y-m-d') : '')) }}">
                        @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Wafat <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_wafat" class="form-control @error('tanggal_wafat') is-invalid @enderror" value="{{ old('tanggal_wafat', $permohonan->tanggal_wafat ? \Carbon\Carbon::parse($permohonan->tanggal_wafat)->format('Y-m-d') : ($linkedJenazah?->tanggal_wafat ? \Carbon\Carbon::parse($linkedJenazah->tanggal_wafat)->format('Y-m-d') : '')) }}">
                        @error('tanggal_wafat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3">{{ old('alamat', $permohonan->alamat ?? $linkedJenazah?->alamat) }}</textarea>
                        @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                @if($isPerpanjangan)
                    <div class="row g-3 mt-3">
                        <div class="col-12">
                            <h6 class="fw-bold mb-0">Data Pemakaman</h6>
                        </div>

                        <div class="col-12">
                            <div class="p-3 rounded-4" style="background:#f8fafc;border:1px solid #d0d5dd;">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="small text-muted">Kode Makam</div>
                                        <div class="fw-semibold">{{ $linkedMakam?->kode_makam ?? $permohonan->kode_makam ?? '-' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted">Blok</div>
                                        <div class="fw-semibold">{{ $linkedMakam?->blok ?? $permohonan->blok ?? '-' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted">Zona</div>
                                        <div class="fw-semibold">{{ $linkedMakam?->zona ?? $permohonan->zona ?? '-' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted">Nomor Makam</div>
                                        <div class="fw-semibold">{{ $linkedMakam?->nomor ?? $permohonan->nomor_makam ?? '-' }}</div>
                                    </div>
                                    <div class="col-12">
                                        <div class="small text-muted">Keterangan Makam</div>
                                        <div class="fw-semibold">{{ $linkedMakam?->keterangan ?? $permohonan->keterangan ?? '-' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted">Tenggat Sewa Saat Ini</div>
                                        <div class="fw-semibold">{{ $renewalDueAt ? $renewalDueAt->format('d F Y') : '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tenggat Sewa Baru <span class="text-danger">*</span></label>
                            <input type="date" name="tenggat_sewa_makam" class="form-control @error('tenggat_sewa_makam') is-invalid @enderror" value="{{ old('tenggat_sewa_makam', optional($permohonan->tenggat_sewa_makam)->format('Y-m-d')) }}">
                            <small class="text-muted d-block mt-1">Tanggal ini akan digunakan sebagai tenggat sewa terbaru setelah disimpan.</small>
                            @error('tenggat_sewa_makam')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                @endif

                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <h6 class="fw-bold mb-0">Data Ahli Waris</h6>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nama Ahli Waris</label>
                        <input type="text" name="nama_ahli_waris" class="form-control @error('nama_ahli_waris') is-invalid @enderror" value="{{ old('nama_ahli_waris', $permohonan->nama_ahli_waris) }}">
                        @error('nama_ahli_waris')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">No HP Ahli Waris</label>
                        <input type="text" name="no_hp_ahli_waris" class="form-control @error('no_hp_ahli_waris') is-invalid @enderror" value="{{ old('no_hp_ahli_waris', $permohonan->no_hp_ahli_waris) }}">
                        @error('no_hp_ahli_waris')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Hubungan Keluarga</label>
                        <input type="text" name="hubungan_keluarga" class="form-control @error('hubungan_keluarga') is-invalid @enderror" value="{{ old('hubungan_keluarga', $permohonan->hubungan_keluarga) }}">
                        @error('hubungan_keluarga')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3">{{ old('catatan', $permohonan->catatan) }}</textarea>
                        @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <h6 class="fw-bold mb-0">Upload Dokumen</h6>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Scan KTP Ahli Waris</label>
                        <input type="file" name="scan_ktp_ahli_waris" class="form-control @error('scan_ktp_ahli_waris') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                        @if($permohonan->scan_ktp_ahli_waris)
                            <small class="text-muted d-block mt-1">Saat ini: <a href="{{ asset('storage/' . $permohonan->scan_ktp_ahli_waris) }}" target="_blank">Lihat file</a></small>
                        @endif
                        @error('scan_ktp_ahli_waris')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Scan KK</label>
                        <input type="file" name="scan_kk" class="form-control @error('scan_kk') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                        @if($permohonan->scan_kk)
                            <small class="text-muted d-block mt-1">Saat ini: <a href="{{ asset('storage/' . $permohonan->scan_kk) }}" target="_blank">Lihat file</a></small>
                        @endif
                        @error('scan_kk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Surat Kematian</label>
                        <input type="file" name="surat_kematian" class="form-control @error('surat_kematian') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                        @if($permohonan->surat_kematian)
                            <small class="text-muted d-block mt-1">Saat ini: <a href="{{ asset('storage/' . $permohonan->surat_kematian) }}" target="_blank">Lihat file</a></small>
                        @endif
                        @error('surat_kematian')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn" style="background-color:#1E3E62;color:white;">
                        <i class="bi bi-check-circle"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
