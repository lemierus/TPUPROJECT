@extends('admin.layouts.app')

@section('title', 'Buat Permohonan Petugas')

@section('content')
@php
$jenis = old('jenis_permohonan', 'makam_baru');
$isPerpanjangan = $jenis === 'perpanjangan';
@endphp

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

                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <h6 class="fw-bold mb-2">Jenis Permohonan</h6>
                    </div>

                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jenis_permohonan" id="jenis_makam_baru" value="makam_baru" @checked($jenis==='makam_baru' ) onchange="toggleJenisPermohonan('makam_baru')">
                            <label class="form-check-label" for="jenis_makam_baru">Makam Baru</label>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jenis_permohonan" id="jenis_perpanjangan" value="perpanjangan" @checked($jenis==='perpanjangan' ) onchange="toggleJenisPermohonan('perpanjangan')">
                            <label class="form-check-label" for="jenis_perpanjangan">Perpanjangan</label>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jenis_permohonan" id="jenis_pemindahan" value="pemindahan_makam" @checked($jenis==='pemindahan_makam' ) onchange="toggleJenisPermohonan('pemindahan_makam')">
                            <label class="form-check-label" for="jenis_pemindahan">Pemindahan Makam</label>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jenis_permohonan" id="jenis_renovasi" value="renovasi_makam" @checked($jenis==='renovasi_makam' ) onchange="toggleJenisPermohonan('renovasi_makam')">
                            <label class="form-check-label" for="jenis_renovasi">Renovasi Makam</label>
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
                            <input type="text" name="nik_jenazah" class="form-control" value="{{ old('nik_jenazah') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nama Jenazah <span class="text-danger">*</span></label>
                            <input type="text" name="nama_jenazah" class="form-control" value="{{ old('nama_jenazah') }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tanggal Wafat</label>
                            <input type="date" name="tanggal_wafat" class="form-control" value="{{ old('tanggal_wafat') }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="">Pilih</option>
                                <option value="Laki-laki" @selected(old('jenis_kelamin')==='Laki-laki' )>Laki-laki</option>
                                <option value="Perempuan" @selected(old('jenis_kelamin')==='Perempuan' )>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Agama</label>
                            <input type="text" name="agama" class="form-control" value="{{ old('agama') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="3">{{ old('alamat') }}</textarea>
                        </div>
                    </div>
                </div>

                <div id="section-data-makam" class="jenis-section" style="display: {{ $isPerpanjangan ? 'block' : 'block' }};">
                    <div class="row g-3 mt-3">
                        <div class="col-12">
                            <h6 class="fw-bold mb-0">Data Makam</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Pilih Makam</label>
                            <select id="makam_id" name="makam_id" class="form-select">
                                <option value="">Pilih makam</option>
                                @foreach($makams as $makam)
                                <option value="{{ $makam->id }}"
                                    data-kode="{{ $makam->kode_makam }}"
                                    data-blok="{{ $makam->blok }}"
                                    data-zona="{{ $makam->zona }}"
                                    data-nomor="{{ $makam->nomor }}"
                                    data-keterangan="{{ $makam->keterangan }}"
                                    @selected(old('makam_id')==$makam->id)>
                                    {{ $makam->kode_makam }} - Blok {{ $makam->blok ?? '-' }} / Zona {{ $makam->zona ?? '-' }} / No {{ $makam->nomor ?? '-' }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Kode Makam</label>
                            <input type="text" id="kode_makam" name="kode_makam" class="form-control" value="{{ old('kode_makam') }}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Blok</label>
                            <input type="text" id="blok" name="blok" class="form-control" value="{{ old('blok') }}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Zona</label>
                            <input type="text" id="zona" name="zona" class="form-control" value="{{ old('zona') }}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Nomor Makam</label>
                            <input type="text" id="nomor_makam" name="nomor_makam" class="form-control" value="{{ old('nomor_makam') }}" readonly>
                        </div>

                        <div id="perpanjangan-extra" class="row g-3 mt-1" style="display: {{ $isPerpanjangan ? 'block' : 'none' }};">
                            <div class="col-md-4">
                                <label class="form-label">No. Makam</label>
                                <input type="text" name="no_makam" class="form-control" value="{{ old('no_makam') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Blok / Zona Makam</label>
                                <input type="text" name="blok_zona_makam" class="form-control" value="{{ old('blok_zona_makam') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tahun Pemakaman</label>
                                <input type="number" name="tahun_pemakaman" class="form-control" value="{{ old('tahun_pemakaman') }}" min="1900" max="{{ now()->year }}">
                            </div>
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
                        <input type="file" name="scan_ktp_ahli_waris" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Scan KK</label>
                        <input type="file" name="scan_kk" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Surat Kematian</label>
                        <input type="file" name="surat_kematian" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>

                    <div id="bukti-retribusi-wrapper" class="col-md-4" style="display: {{ $isPerpanjangan ? 'block' : 'none' }};">
                        <label class="form-label">Bukti Pembayaran Retribusi</label>
                        <input type="file" name="bukti_pembayaran_retribusi" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn" style="background-color:#1E3E62;color:white;">
                        <i class="bi bi-send"></i> Simpan Permohonan
                    </button>
                </div>
            </form>

            <script>
                function toggleJenisPermohonan(jenis) {
                    const jenazahSection = document.getElementById('section-data-jenazah');
                    const perpanjanganExtra = document.getElementById('perpanjangan-extra');
                    const buktiWrapper = document.getElementById('bukti-retribusi-wrapper');

                    if (jenis === 'perpanjangan') {
                        jenazahSection.style.display = 'none';
                        perpanjanganExtra.style.display = 'block';
                        buktiWrapper.style.display = 'block';
                    } else {
                        jenazahSection.style.display = 'block';
                        perpanjanganExtra.style.display = 'none';
                        buktiWrapper.style.display = 'none';
                    }
                }

                const makamSelect = document.getElementById('makam_id');
                const kodeMakam = document.getElementById('kode_makam');
                const blok = document.getElementById('blok');
                const zona = document.getElementById('zona');
                const nomorMakam = document.getElementById('nomor_makam');

                function syncMakamFields() {
                    const selected = makamSelect.options[makamSelect.selectedIndex];

                    if (!selected || !selected.value) {
                        kodeMakam.value = '';
                        blok.value = '';
                        zona.value = '';
                        nomorMakam.value = '';
                        return;
                    }

                    kodeMakam.value = selected.dataset.kode || '';
                    blok.value = selected.dataset.blok || '';
                    zona.value = selected.dataset.zona || '';
                    nomorMakam.value = selected.dataset.nomor || '';
                }

                makamSelect.addEventListener('change', syncMakamFields);
                syncMakamFields();
            </script>
        </div>
    </div>
</div>
@endsection