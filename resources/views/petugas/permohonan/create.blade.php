@extends('admin.layouts.app')

@section('title', 'Buat Permohonan Petugas')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Buat Permohonan Petugas</h4>
            <p class="text-muted mb-0">TPU: {{ auth()->user()->tpu }}</p>
        </div>
        <a href="{{ route('petugas.permohonan') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body px-4 py-4">
            <form action="{{ route('petugas.permohonan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="tpu" value="{{ auth()->user()->tpu }}">
                <input type="hidden" name="jenis_permohonan" value="makam_baru">

                <div class="row g-3">
                    <div class="col-12">
                        <h6 class="fw-bold mb-0">Data Jenazah</h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">NIK Jenazah <span class="text-danger">*</span></label>
                        <input type="text" name="nik_jenazah" class="form-control @error('nik_jenazah') is-invalid @enderror" value="{{ old('nik_jenazah') }}">
                        @error('nik_jenazah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nama Jenazah <span class="text-danger">*</span></label>
                        <input type="text" name="nama_jenazah" class="form-control @error('nama_jenazah') is-invalid @enderror" value="{{ old('nama_jenazah') }}">
                        @error('nama_jenazah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror" value="{{ old('tempat_lahir') }}">
                        @error('tempat_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
                            <option value="">Pilih</option>
                            <option value="Laki-laki" @selected(old('jenis_kelamin') === 'Laki-laki')>Laki-laki</option>
                            <option value="Perempuan" @selected(old('jenis_kelamin') === 'Perempuan')>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Agama</label>
                        <input type="text" name="agama" class="form-control @error('agama') is-invalid @enderror" value="{{ old('agama') }}">
                        @error('agama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir') }}">
                        @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Wafat <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_wafat" class="form-control @error('tanggal_wafat') is-invalid @enderror" value="{{ old('tanggal_wafat') }}">
                        @error('tanggal_wafat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3">{{ old('alamat') }}</textarea>
                        @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <h6 class="fw-bold mb-0">Data Ahli Waris</h6>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nama Ahli Waris</label>
                        <input type="text" name="nama_ahli_waris" class="form-control @error('nama_ahli_waris') is-invalid @enderror" value="{{ old('nama_ahli_waris', auth()->user()->name) }}">
                        @error('nama_ahli_waris')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">No HP Ahli Waris</label>
                        <input type="text" name="no_hp_ahli_waris" class="form-control @error('no_hp_ahli_waris') is-invalid @enderror" value="{{ old('no_hp_ahli_waris') }}">
                        @error('no_hp_ahli_waris')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Hubungan Keluarga</label>
                        <input type="text" name="hubungan_keluarga" class="form-control @error('hubungan_keluarga') is-invalid @enderror" value="{{ old('hubungan_keluarga') }}">
                        @error('hubungan_keluarga')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3">{{ old('catatan') }}</textarea>
                        @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-12">
                        <h6 class="fw-bold mb-0">Upload Dokumen</h6>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Scan KTP Ahli Waris</label>
                        <input type="file" name="scan_ktp_ahli_waris" class="form-control @error('scan_ktp_ahli_waris') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf" required>
                        @error('scan_ktp_ahli_waris')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Scan KK</label>
                        <input type="file" name="scan_kk" class="form-control @error('scan_kk') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf" required>
                        @error('scan_kk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Surat Kematian</label>
                        <input type="file" name="surat_kematian" class="form-control @error('surat_kematian') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf" required>
                        @error('surat_kematian')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Bukti Pembayaran Retribusi</label>
                        <input type="file" name="bukti_pembayaran_retribusi" class="form-control @error('bukti_pembayaran_retribusi') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                        @error('bukti_pembayaran_retribusi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn" style="background-color:#1E3E62;color:white;">
                        <i class="bi bi-send"></i> Simpan Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
