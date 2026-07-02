@extends('admin.layouts.app')

@section('title', 'Edit Data Jenazah')

@section('content')
@php
    $routePrefix = request()->routeIs('petugas.*') ? 'petugas' : 'admin';
    $selectedMakamId = old('makam_id', $jenazah->makam_id);
    $selectedMakam = $jenazah->relationLoaded('makam') ? $jenazah->makam : null;
    $linkedPermohonan = $jenazah->relationLoaded('permohonan') ? $jenazah->permohonan : $jenazah->permohonan;
    $displayRenewalAt = $jenazah->renewalDueAt();
@endphp

<div class="container-fluid pt-2 pb-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Edit Data Jenazah</h4>
            <p class="text-muted mb-0">Perbarui informasi data jenazah</p>
        </div>
        <div>
            <span class="badge px-3 py-2" style="background-color: #1E3E62; color: white;">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
    </div>

    {{-- FORM --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body px-4 py-4">

            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="p-3 rounded-4" style="background:#f8fafc;border:1px solid #d0d5dd;">
                        <div class="fw-bold text-dark mb-3">Ringkasan Data Terkait</div>
                        <div class="row g-3">
                            <div class="col-lg-4">
                                <div class="p-3 bg-white rounded-3 h-100 border">
                                    <div class="fw-semibold mb-2">Data Jenazah</div>
                                    <div class="small text-muted">Nama</div>
                                    <div class="mb-2">{{ $jenazah->nama ?? '-' }}</div>
                                    <div class="small text-muted">NIK</div>
                                    <div class="mb-2">{{ $jenazah->nik ?? '-' }}</div>
                                    <div class="small text-muted">Jenis Kelamin</div>
                                    <div class="mb-2">{{ $jenazah->jenis_kelamin ?? '-' }}</div>
                                    <div class="small text-muted">Agama</div>
                                    <div class="mb-2">{{ $jenazah->agama ?? '-' }}</div>
                                    <div class="small text-muted">Tempat Lahir</div>
                                    <div class="mb-2">{{ $jenazah->tempat_lahir ?? '-' }}</div>
                                    <div class="small text-muted">Tanggal Lahir</div>
                                    <div class="mb-2">{{ $jenazah->tanggal_lahir ? \Carbon\Carbon::parse($jenazah->tanggal_lahir)->format('d F Y') : '-' }}</div>
                                    <div class="small text-muted">Tanggal Wafat</div>
                                    <div>{{ $jenazah->tanggal_wafat ? \Carbon\Carbon::parse($jenazah->tanggal_wafat)->format('d F Y') : '-' }}</div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="p-3 bg-white rounded-3 h-100 border">
                                    <div class="fw-semibold mb-2">Data Ahli Waris</div>
                                    <div class="small text-muted">Nama</div>
                                    <div class="mb-2">{{ $jenazah->nama_ahli_waris ?? $linkedPermohonan?->nama_ahli_waris ?? '-' }}</div>
                                    <div class="small text-muted">No HP</div>
                                    <div class="mb-2">{{ $jenazah->no_hp_ahli_waris ?? $linkedPermohonan?->no_hp_ahli_waris ?? '-' }}</div>
                                    <div class="small text-muted">Hubungan Keluarga</div>
                                    <div class="mb-2">{{ $jenazah->hubungan_keluarga ?? $linkedPermohonan?->hubungan_keluarga ?? '-' }}</div>
                                    <div class="small text-muted">Akun Pemohon</div>
                                    <div class="mb-2">{{ $linkedPermohonan?->user?->name ?? '-' }}</div>
                                    <div class="small text-muted">TPU</div>
                                    <div class="mb-2">{{ $linkedPermohonan?->tpu ?? $jenazah->tpu ?? '-' }}</div>
                                    <div class="small text-muted">Status Permohonan</div>
                                    <div class="mb-2">{{ $linkedPermohonan?->status ?? '-' }}</div>
                                    <div class="small text-muted">Catatan</div>
                                    <div>{{ $jenazah->catatan ?? $linkedPermohonan?->catatan ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="p-3 bg-white rounded-3 h-100 border">
                                    <div class="fw-semibold mb-2">Data Makam</div>
                                    <div class="small text-muted">Kode Makam</div>
                                    <div class="mb-2">{{ $selectedMakam?->kode_makam ?? $jenazah->kode_makam ?? '-' }}</div>
                                    <div class="small text-muted">Blok</div>
                                    <div class="mb-2">{{ $selectedMakam?->blok ?? $jenazah->blok ?? '-' }}</div>
                                    <div class="small text-muted">Zona</div>
                                    <div class="mb-2">{{ $selectedMakam?->zona ?? $jenazah->zona ?? '-' }}</div>
                                    <div class="small text-muted">Nomor</div>
                                    <div class="mb-2">{{ $selectedMakam?->nomor ?? $jenazah->nomor_makam ?? '-' }}</div>
                                    <div class="small text-muted">Keterangan</div>
                                    <div class="mb-2">{{ $selectedMakam?->keterangan ?? $jenazah->keterangan ?? '-' }}</div>
                                    <div class="small text-muted">Tenggat Sewa Makam</div>
                                    <div>
                                        @if($displayRenewalAt)
                                            {{ $displayRenewalAt->format('d F Y') }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route($routePrefix.'.data-jenazah.update', $jenazah->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">

                    {{-- NIK --}}
                    <div class="col-md-6">
                        <label class="form-label">NIK</label>
                        <input type="text" name="nik"
                               class="form-control @error('nik') is-invalid @enderror"
                               value="{{ old('nik', $jenazah->nik) }}"
                               placeholder="Masukkan NIK">

                        @error('nik')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nama --}}
                    <div class="col-md-6">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama"
                               class="form-control @error('nama') is-invalid @enderror"
                               value="{{ old('nama', $jenazah->nama) }}"
                               placeholder="Masukkan nama">

                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Alamat --}}
                    <div class="col-12">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat"
                                  class="form-control @error('alamat') is-invalid @enderror"
                                  rows="3"
                                  placeholder="Masukkan alamat lengkap">{{ old('alamat', $jenazah->alamat) }}</textarea>

                        @error('alamat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Makam --}}
                    <div class="col-md-6">
                        <label class="form-label">Makam</label>
                        <select id="makam_id" name="makam_id"
                                class="form-select @error('makam_id') is-invalid @enderror">
                            <option value="">Belum dipilih</option>
                            @foreach($makams as $makam)
                                <option value="{{ $makam->id }}"
                                    @selected($selectedMakamId == $makam->id)
                                    data-kode-makam="{{ $makam->kode_makam }}"
                                    data-blok="{{ $makam->blok }}"
                                    data-zona="{{ $makam->zona }}"
                                    data-nomor-makam="{{ $makam->nomor }}"
                                    data-keterangan="{{ $makam->keterangan }}">
                                    {{ $makam->kode_makam }} - {{ $makam->blok ?? '-' }} / {{ $makam->zona ?? '-' }}
                                </option>
                            @endforeach
                        </select>

                        @error('makam_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Kode Makam --}}
                    <div class="col-md-6">
                        <label class="form-label">Kode Makam</label>
                        <input type="text" id="kode_makam" name="kode_makam"
                               class="form-control @error('kode_makam') is-invalid @enderror"
                               value="{{ old('kode_makam', $jenazah->kode_makam ?? $selectedMakam?->kode_makam) }}"
                               placeholder="Otomatis terisi saat makam dipilih">

                        @error('kode_makam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Blok --}}
                    <div class="col-md-4">
                        <label class="form-label">Blok</label>
                        <input type="text" id="blok" name="blok"
                               class="form-control @error('blok') is-invalid @enderror"
                               value="{{ old('blok', $jenazah->blok ?? $selectedMakam?->blok) }}"
                               placeholder="Otomatis terisi saat makam dipilih">

                        @error('blok')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Zona --}}
                    <div class="col-md-4">
                        <label class="form-label">Zona</label>
                        <input type="text" id="zona" name="zona"
                               class="form-control @error('zona') is-invalid @enderror"
                               value="{{ old('zona', $jenazah->zona ?? $selectedMakam?->zona) }}"
                               placeholder="Otomatis terisi saat makam dipilih">

                        @error('zona')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nomor --}}
                    <div class="col-md-4">
                        <label class="form-label">Nomor</label>
                        <input type="text" id="nomor_makam" name="nomor_makam"
                               class="form-control @error('nomor_makam') is-invalid @enderror"
                               value="{{ old('nomor_makam', $jenazah->nomor_makam ?? $selectedMakam?->nomor) }}"
                               placeholder="Otomatis terisi saat makam dipilih">

                        @error('nomor_makam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Keterangan --}}
                    <div class="col-12">
                        <label class="form-label">Keterangan Makam</label>
                        <textarea id="keterangan" name="keterangan"
                                  class="form-control @error('keterangan') is-invalid @enderror"
                                  rows="3"
                                  placeholder="Otomatis terisi saat makam dipilih">{{ old('keterangan', $jenazah->keterangan ?? $selectedMakam?->keterangan) }}</textarea>

                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tenggat Masa Sewa Makam</label>
                        <input type="date" name="tenggat_sewa_makam"
                               class="form-control @error('tenggat_sewa_makam') is-invalid @enderror"
                               value="{{ old('tenggat_sewa_makam', optional($jenazah->renewalDueAt())->format('Y-m-d')) }}">

                        <small class="text-muted d-block mt-1">Tanggal ini dipakai sebagai batas akhir sewa makam untuk data jenazah ini.</small>

                        @error('tenggat_sewa_makam')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div class="col-md-6">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin"
                                class="form-select @error('jenis_kelamin') is-invalid @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki"
                                {{ old('jenis_kelamin', $jenazah->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>
                                Laki-laki
                            </option>
                            <option value="Perempuan"
                                {{ old('jenis_kelamin', $jenazah->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>
                                Perempuan
                            </option>
                        </select>

                        @error('jenis_kelamin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tanggal Wafat --}}
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Wafat</label>
                        <input type="date" name="tanggal_wafat"
                               class="form-control @error('tanggal_wafat') is-invalid @enderror"
                               value="{{ old('tanggal_wafat', $jenazah->tanggal_wafat) }}">

                        @error('tanggal_wafat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- BUTTON --}}
                <div class="d-flex justify-content-between mt-4">

                    <a href="{{ route($routePrefix.'.data-jenazah') }}"
                       class="btn btn-outline-secondary rounded-3">
                        ← Kembali
                    </a>

                    <button type="submit"
                            class="btn rounded-3"
                            style="background-color:#1E3E62; color:white;">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const makamSelect = document.getElementById('makam_id');
    if (!makamSelect) {
        return;
    }

    const fields = {
        kodeMakam: document.getElementById('kode_makam'),
        blok: document.getElementById('blok'),
        zona: document.getElementById('zona'),
        nomorMakam: document.getElementById('nomor_makam'),
        keterangan: document.getElementById('keterangan'),
    };

    const fillFields = () => {
        const option = makamSelect.selectedOptions[0];
        if (!option || !makamSelect.value) {
            return;
        }

        if (fields.kodeMakam) fields.kodeMakam.value = option.dataset.kodeMakam || '';
        if (fields.blok) fields.blok.value = option.dataset.blok || '';
        if (fields.zona) fields.zona.value = option.dataset.zona || '';
        if (fields.nomorMakam) fields.nomorMakam.value = option.dataset.nomorMakam || '';
        if (fields.keterangan) fields.keterangan.value = option.dataset.keterangan || '';
    };

    makamSelect.addEventListener('change', fillFields);
    fillFields();
});
</script>
@endpush
