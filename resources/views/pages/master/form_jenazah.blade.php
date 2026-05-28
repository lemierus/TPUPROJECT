@extends('admin.layouts.app')

@php
    $isEdit = $jenazah->exists;
    $routePrefix = request()->routeIs('petugas.*') ? 'petugas' : 'admin';
@endphp

@section('title', $isEdit ? 'Edit Data Jenazah' : 'Tambah Data Jenazah')

@section('content')
<div class="container-fluid pt-2 pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">{{ $isEdit ? 'Edit Data Jenazah' : 'Tambah Data Jenazah' }}</h4>
            <p class="text-muted mb-0">Lengkapi informasi jenazah dan data pemakaman secara ringkas dan akurat.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body px-4 py-4">
            <form action="{{ $isEdit ? route($routePrefix.'.data-jenazah.update', $jenazah->id) : route($routePrefix.'.data-jenazah.store') }}" method="POST">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="border rounded-3 bg-light-subtle p-3 p-md-4 mb-4">
                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-1">Informasi Jenazah</h6>
                        <small class="text-muted">Field disesuaikan dengan data yang tampil di halaman daftar jenazah.</small>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">NIK</label>
                            <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik', $jenazah->nik) }}" required>
                            @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $jenazah->nama) }}" required>
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror" value="{{ old('tempat_lahir', $jenazah->tempat_lahir) }}">
                            @error('tempat_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir', optional($jenazah->tanggal_lahir)->format('Y-m-d')) }}">
                            @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tanggal Wafat</label>
                            <input type="date" name="tanggal_wafat" class="form-control @error('tanggal_wafat') is-invalid @enderror" value="{{ old('tanggal_wafat', optional($jenazah->tanggal_wafat)->format('Y-m-d')) }}" required>
                            @error('tanggal_wafat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                                <option value="">Pilih</option>
                                <option value="Laki-laki" @selected(in_array(old('jenis_kelamin', $jenazah->jenis_kelamin), ['L', 'Laki-laki'], true))>Laki-laki</option>
                                <option value="Perempuan" @selected(in_array(old('jenis_kelamin', $jenazah->jenis_kelamin), ['P', 'Perempuan'], true))>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Agama</label>
                            <input type="text" name="agama" class="form-control @error('agama') is-invalid @enderror" value="{{ old('agama', $jenazah->agama) }}">
                            @error('agama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" value="{{ old('keterangan', $jenazah->keterangan) }}">
                            @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="border rounded-3 bg-light-subtle p-3 p-md-4 mb-4">
                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-1">Informasi Ahli Waris</h6>
                        <small class="text-muted">Edit data ahli waris yang terkait dengan jenazah ini.</small>
                    </div>

                    @php $relatedPermohonan = $jenazah->permohonan ?? null; @endphp
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Ahli Waris</label>
                            <input type="text" name="nama_ahli_waris" class="form-control @error('nama_ahli_waris') is-invalid @enderror" value="{{ old('nama_ahli_waris', $relatedPermohonan?->nama_ahli_waris) }}">
                            @error('nama_ahli_waris')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Hubungan Keluarga</label>
                            <input type="text" name="hubungan_keluarga" class="form-control @error('hubungan_keluarga') is-invalid @enderror" value="{{ old('hubungan_keluarga', $relatedPermohonan?->hubungan_keluarga) }}">
                            @error('hubungan_keluarga')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">No HP Ahli Waris</label>
                            <input type="text" name="no_hp_ahli_waris" class="form-control @error('no_hp_ahli_waris') is-invalid @enderror" value="{{ old('no_hp_ahli_waris', $relatedPermohonan?->no_hp_ahli_waris) }}">
                            @error('no_hp_ahli_waris')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Catatan</label>
                            <input type="text" name="catatan" class="form-control @error('catatan') is-invalid @enderror" value="{{ old('catatan', $relatedPermohonan?->catatan) }}">
                            @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="border rounded-3 p-3 p-md-4">
                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-1">Informasi Pemakaman</h6>
                        <small class="text-muted">Pilih makam yang tersedia dan lengkapi alamat bila dibutuhkan.</small>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Makam</label>
                            <select name="makam_id" class="form-select @error('makam_id') is-invalid @enderror">
                                <option value="">Belum dipilih</option>
                                @foreach($makams as $makam)
                                    <option value="{{ $makam->id }}" @selected(old('makam_id', $jenazah->makam_id) == $makam->id)>
                                        {{ $makam->kode_makam }} - {{ $makam->blok ?? '-' }} / {{ $makam->zona ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('makam_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3">{{ old('alamat', $jenazah->alamat) }}</textarea>
                            @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route($routePrefix.'.data-jenazah') }}" class="btn btn-outline-secondary">Kembali</a>
                    <button type="submit" class="btn" style="background-color:#1E3E62;color:white;">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
