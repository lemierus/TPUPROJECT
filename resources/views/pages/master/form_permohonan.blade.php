@extends('admin.layouts.app')

@php
    $isEdit = $permohonan->exists;
    $routePrefix = request()->routeIs('petugas.*') ? 'petugas' : 'admin';
    $isPetugas = auth()->user()?->isPetugas();
    $isPerpanjangan = old('jenis_permohonan', $permohonan->jenis_permohonan) === 'perpanjangan';
    $isAdmin = auth()->user()?->isAdmin();
@endphp

@section('title', $isEdit ? 'Edit Permohonan' : 'Tambah Permohonan')

@section('content')
<div class="container-fluid pt-2 pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">{{ $isEdit ? 'Edit Permohonan' : 'Tambah Permohonan' }}</h4>
            <p class="text-muted mb-0">Kelola data permohonan ahli waris.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body px-4 py-4">
            <form action="{{ $isEdit ? route($routePrefix.'.master.permohonan.update-data', $permohonan) : route($routePrefix.'.master.permohonan.store') }}" method="POST">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    @if($isPerpanjangan)
                    <div class="col-12">
                        <div class="border rounded-3 bg-light-subtle p-3">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Data Jenazah Terintegrasi</h6>
                                    <small class="text-muted">Untuk perpanjangan makam, data jenazah otomatis diambil dari data jenazah yang sudah terhubung dengan makam atau data sebelumnya.</small>
                                </div>
                                @if($permohonan->jenazah_id)
                                    <span class="badge bg-success">Tersinkron</span>
                                @else
                                    <span class="badge bg-warning text-dark">Belum ditemukan</span>
                                @endif
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Nama Jenazah</small>
                                    <strong>{{ $permohonan->nama_jenazah ?? '-' }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">NIK Jenazah</small>
                                    <strong>{{ $permohonan->nik_jenazah ?? '-' }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Jenis Kelamin / Agama</small>
                                    <strong>
                                        {{ $permohonan->jenis_kelamin ?? '-' }}
                                        @if($permohonan->agama)
                                            / {{ $permohonan->agama }}
                                        @endif
                                    </strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Tempat / Tanggal Lahir</small>
                                    <strong>
                                        {{ $permohonan->tempat_lahir ?? '-' }}
                                        /
                                        {{ $permohonan->tanggal_lahir ? \Carbon\Carbon::parse($permohonan->tanggal_lahir)->format('d-m-Y') : '-' }}
                                    </strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Tanggal Wafat</small>
                                    <strong>{{ $permohonan->tanggal_wafat ? \Carbon\Carbon::parse($permohonan->tanggal_wafat)->format('d-m-Y') : '-' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="col-md-6">
                        <label class="form-label">TPU</label>
                        <select name="tpu" class="form-select @error('tpu') is-invalid @enderror" required {{ $isPetugas ? 'disabled' : '' }}>
                            <option value="">Pilih TPU</option>
                            @foreach($tpuOptions ?? ['TPU Tunggul Hitam', 'TPU Bungus Teluk Kabung', 'TPU Air Dingin'] as $tpu)
                                <option value="{{ $tpu }}" @selected(old('tpu', $permohonan->tpu ?? $selectedTpu ?? auth()->user()->tpu) === $tpu)>{{ $tpu }}</option>
                            @endforeach
                        </select>
                        @if($isPetugas)
                            <input type="hidden" name="tpu" value="{{ auth()->user()->tpu }}">
                        @endif
                        @error('tpu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Pemohon</label>
                        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">Pilih pemohon</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(old('user_id', $permohonan->user_id) == $user->id)>
                                    {{ $user->name }} - {{ $user->email }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jenazah</label>
                        <select name="jenazah_id" class="form-select @error('jenazah_id') is-invalid @enderror" required>
                            <option value="">Pilih jenazah</option>
                            @foreach($jenazah as $item)
                                <option value="{{ $item->id }}" @selected(old('jenazah_id', $permohonan->jenazah_id) == $item->id)>
                                    {{ $item->nama }} - {{ $item->nik }}
                                </option>
                            @endforeach
                        </select>
                        @if($isPerpanjangan)
                            <small class="text-muted">Pilihan ini akan terisi otomatis saat data makam atau data jenazah terkait berhasil ditemukan.</small>
                        @endif
                        @error('jenazah_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="menunggu" @selected(old('status', $permohonan->status ?: 'menunggu') === 'menunggu')>Menunggu</option>
                            <option value="disetujui" @selected(old('status', $permohonan->status) === 'disetujui')>Disetujui</option>
                            <option value="ditolak" @selected(old('status', $permohonan->status) === 'ditolak')>Ditolak</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror" rows="3">{{ old('catatan', $permohonan->catatan) }}</textarea>
                        @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
