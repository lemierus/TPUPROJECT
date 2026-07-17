@extends('admin.layouts.app')

@section('title', 'Lengkapi Dokumen Pemakaman')

@section('content')
<div class="container-fluid py-4">
    @if($errors->any())
        <div class="alert alert-danger border-2 border-dark shadow-sm mb-4">
            <i class="bi bi-exclamation-circle-fill me-2"></i>{{ $errors->first() }}
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="mb-4">
                <h4 class="fw-bold mb-1">Lengkapi Dokumen Pemakaman</h4>
                <p class="text-muted mb-0">
                    Lengkapi dokumen untuk permohonan darurat atas nama jenazah <strong>{{ $permohonan->nama_jenazah }}</strong>.
                </p>
            </div>

            @if($permohonan->catatan_revisi)
                <div class="alert alert-warning border-2 border-dark shadow-sm mb-4">
                    <i class="bi bi-pencil-square me-2"></i>
                    <strong>Catatan revisi dari petugas:</strong> {{ $permohonan->catatan_revisi }}
                </div>
            @endif

            <form action="{{ route('user.permohonan.update-dokumen', $permohonan) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
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
                        <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', optional($permohonan->tanggal_lahir)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $permohonan->alamat) }}</textarea>
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
                    <div class="col-12">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea name="catatan" class="form-control" rows="3">{{ old('catatan', $permohonan->catatan) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn" style="background:#1E3E62;color:#fff;">
                        <i class="bi bi-check2-circle me-1"></i> Kirim Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
