@extends('admin.layouts.app')

@section('title', 'Edit Permohonan Petugas')

@php
    $jenis = old('jenis_permohonan', $permohonan->jenis_permohonan === 'perpanjangan' ? 'perpanjangan' : 'makam_baru');
    $isPerpanjangan = $jenis === 'perpanjangan';
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Edit Permohonan Petugas</h4>
            <p class="text-muted mb-0">ID: #{{ $permohonan->id }} | TPU: {{ $permohonan->tpu }}</p>
        </div>
        <a href="{{ route('petugas.permohonan.show', $permohonan) }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
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

                <div class="row g-3">
                    <div class="col-12">
                        <h6 class="fw-bold mb-2">Jenis Permohonan</h6>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jenis_permohonan" id="jenis_makam_baru" value="makam_baru" @checked($jenis === 'makam_baru') onchange="toggleJenisPermohonan('makam_baru')">
                            <label class="form-check-label" for="jenis_makam_baru">Pembuatan Makam Baru</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jenis_permohonan" id="jenis_perpanjangan" value="perpanjangan" @checked($jenis === 'perpanjangan') onchange="toggleJenisPermohonan('perpanjangan')">
                            <label class="form-check-label" for="jenis_perpanjangan">Perpanjangan Makam Lama</label>
                        </div>
                    </div>
                </div>

                <div id="section-data-jenazah" class="jenis-section" style="display: {{ $isPerpanjangan ? 'none' : 'block' }};">
                    <div class="row g-3 mt-3">
                        <div class="col-12">
                            <h6 class="fw-bold mb-0">Data Jenazah</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">NIK Jenazah <span class="text-danger">*</span></label>
                            <input type="text" name="nik_jenazah" class="form-control @error('nik_jenazah') is-invalid @enderror" value="{{ old('nik_jenazah', $permohonan->nik_jenazah) }}">
                            @error('nik_jenazah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nama Jenazah <span class="text-danger">*</span></label>
                            <input type="text" name="nama_jenazah" class="form-control @error('nama_jenazah') is-invalid @enderror" value="{{ old('nama_jenazah', $permohonan->nama_jenazah) }}">
                            @error('nama_jenazah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror" value="{{ old('tempat_lahir', $permohonan->tempat_lahir) }}">
                            @error('tempat_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
                                <option value="">Pilih</option>
                                <option value="Laki-laki" @selected(old('jenis_kelamin', $permohonan->jenis_kelamin) === 'Laki-laki')>Laki-laki</option>
                                <option value="Perempuan" @selected(old('jenis_kelamin', $permohonan->jenis_kelamin) === 'Perempuan')>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Agama</label>
                            <input type="text" name="agama" class="form-control @error('agama') is-invalid @enderror" value="{{ old('agama', $permohonan->agama) }}">
                            @error('agama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir', $permohonan->tanggal_lahir ? \Carbon\Carbon::parse($permohonan->tanggal_lahir)->format('Y-m-d') : '') }}">
                            @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tanggal Wafat <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_wafat" class="form-control @error('tanggal_wafat') is-invalid @enderror" value="{{ old('tanggal_wafat', $permohonan->tanggal_wafat ? \Carbon\Carbon::parse($permohonan->tanggal_wafat)->format('Y-m-d') : '') }}">
                            @error('tanggal_wafat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3">{{ old('alamat', $permohonan->alamat) }}</textarea>
                            @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div id="section-data-makam" class="jenis-section" style="display: {{ $isPerpanjangan ? 'block' : 'none' }};">
                    <div class="row g-3 mt-3">
                        <div class="col-12">
                            <h6 class="fw-bold mb-0">Data Makam Lama</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Pilih Makam</label>
                            <select id="makam_id" name="makam_id" class="form-select @error('makam_id') is-invalid @enderror">
                                <option value="">Pilih makam</option>
                                @foreach($makams as $makam)
                                    <option value="{{ $makam->id }}" data-kode="{{ $makam->kode_makam }}" data-blok="{{ $makam->blok }}" data-zona="{{ $makam->zona }}" data-nomor="{{ $makam->nomor }}" @selected(old('makam_id', $permohonan->makam_id) == $makam->id)>
                                        {{ $makam->kode_makam }} - Blok {{ $makam->blok ?? '-' }} / Zona {{ $makam->zona ?? '-' }} / No {{ $makam->nomor ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('makam_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">No Makam</label>
                            <input type="text" name="no_makam" class="form-control @error('no_makam') is-invalid @enderror" value="{{ old('no_makam', $permohonan->no_makam) }}">
                            @error('no_makam')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Blok / Zona Makam</label>
                            <input type="text" name="blok_zona_makam" class="form-control @error('blok_zona_makam') is-invalid @enderror" value="{{ old('blok_zona_makam', $permohonan->blok_zona_makam) }}">
                            @error('blok_zona_makam')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tahun Pemakaman</label>
                            <input type="number" name="tahun_pemakaman" class="form-control @error('tahun_pemakaman') is-invalid @enderror" value="{{ old('tahun_pemakaman', $permohonan->tahun_pemakaman) }}" min="1900" max="{{ now()->year }}">
                            @error('tahun_pemakaman')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

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

                    <div class="col-md-4" id="bukti-retribusi-wrapper" style="display: {{ $isPerpanjangan ? 'block' : 'none' }};">
                        <label class="form-label">Bukti Pembayaran Retribusi</label>
                        <input type="file" name="bukti_pembayaran_retribusi" class="form-control @error('bukti_pembayaran_retribusi') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                        @if($permohonan->bukti_pembayaran_retribusi)
                            <small class="text-muted d-block mt-1">Saat ini: <a href="{{ asset('storage/' . $permohonan->bukti_pembayaran_retribusi) }}" target="_blank">Lihat file</a></small>
                        @endif
                        @error('bukti_pembayaran_retribusi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="{{ route('petugas.permohonan.show', $permohonan) }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn" style="background-color:#1E3E62;color:white;">
                        <i class="bi bi-check-circle"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleJenisPermohonan(jenis) {
    const jenazahSection = document.getElementById('section-data-jenazah');
    const makamSection = document.getElementById('section-data-makam');
    const buktiRetribusi = document.getElementById('bukti-retribusi-wrapper');

    const jenazahRequired = ['nik_jenazah', 'nama_jenazah', 'tanggal_wafat', 'tempat_lahir', 'jenis_kelamin', 'agama', 'tanggal_lahir', 'alamat'];
    const makamRequired = ['makam_id', 'no_makam', 'blok_zona_makam'];

    const setRequired = (names, required) => {
        names.forEach((name) => {
            const field = document.querySelector(`[name="${name}"]`);
            if (field) {
                field.required = required;
            }
        });
    };

    if (jenis === 'perpanjangan') {
        jenazahSection.style.display = 'none';
        makamSection.style.display = 'block';
        buktiRetribusi.style.display = 'block';
        setRequired(jenazahRequired, false);
        setRequired(makamRequired, true);
    } else {
        jenazahSection.style.display = 'block';
        makamSection.style.display = 'none';
        buktiRetribusi.style.display = 'none';
        setRequired(jenazahRequired, true);
        setRequired(makamRequired, false);
    }

    syncMakamFields();
}

function syncMakamFields() {
    const makamSelect = document.getElementById('makam_id');
    if (!makamSelect) {
        return;
    }

    const selected = makamSelect.options[makamSelect.selectedIndex];
    if (!selected || !selected.value) {
        return;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const jenis = document.querySelector('input[name="jenis_permohonan"]:checked')?.value || 'makam_baru';
    const makamSelect = document.getElementById('makam_id');

    if (makamSelect) {
        makamSelect.addEventListener('change', syncMakamFields);
    }

    toggleJenisPermohonan(jenis);
});
</script>
@endpush
