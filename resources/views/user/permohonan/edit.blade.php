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
        <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
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

                @if(! $isPerpanjangan)
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
                @endif

                @if($isPerpanjangan)
                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <h6 class="fw-bold mb-0">Ringkasan Data Pemakaman</h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Pilih Nama Jenazah</label>
                        <select name="jenazah_id" id="renewal-jenazah" class="form-select" onchange="fillRenewalFields(this)">
                            <option value="">Pilih jenazah</option>
                            @foreach($perpanjanganJenazahs as $item)
                                <option value="{{ $item->jenazah_id }}"
                                    @selected(old('jenazah_id', $permohonan->jenazah_id) == $item->jenazah_id)
                                    data-makam-id="{{ $item->makam_id }}"
                                    data-nama-jenazah="{{ $item->nama_jenazah ?? $item->jenazah?->nama ?? '' }}"
                                    data-nik-jenazah="{{ $item->nik_jenazah ?? $item->jenazah?->nik ?? '' }}"
                                    data-tempat-lahir="{{ $item->tempat_lahir ?? $item->jenazah?->tempat_lahir ?? '' }}"
                                    data-tanggal-lahir="{{ ! empty($item->tanggal_lahir ?? $item->jenazah?->tanggal_lahir) ? \Illuminate\Support\Carbon::parse($item->tanggal_lahir ?? $item->jenazah?->tanggal_lahir)->format('Y-m-d') : '' }}"
                                    data-tanggal-wafat="{{ ! empty($item->tanggal_wafat ?? $item->jenazah?->tanggal_wafat) ? \Illuminate\Support\Carbon::parse($item->tanggal_wafat ?? $item->jenazah?->tanggal_wafat)->format('Y-m-d') : '' }}"
                                    data-jenis-kelamin="{{ $item->jenis_kelamin ?? $item->jenazah?->jenis_kelamin ?? '' }}"
                                    data-agama="{{ $item->agama ?? $item->jenazah?->agama ?? '' }}"
                                    data-alamat="{{ $item->alamat ?? $item->jenazah?->alamat ?? '' }}"
                                    data-no-makam="{{ $item->makam?->nomor ?? '' }}"
                                    data-blok="{{ $item->makam?->blok ?? '' }}"
                                    data-zona="{{ $item->makam?->zona ?? '' }}"
                                    data-blok-zona="{{ trim(($item->makam?->blok ?? '') . ' / ' . ($item->makam?->zona ?? ''), ' /') }}"
                                    data-tenggat="{{ optional($item->renewalDueAt())->format('Y-m-d') }}"
                                    data-tahun-pemakaman="{{ $item->tahun_pemakaman ?? '' }}">
                                    {{ $item->nama_jenazah ?? $item->jenazah?->nama }}
                                    @if($item->makam)
                                        - {{ $item->makam->kode_makam }} / {{ $item->makam->blok ?? '-' }} / {{ $item->makam->zona ?? '-' }} / No {{ $item->makam->nomor ?? '-' }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Pilih nama jenazah untuk menampilkan data jenazah dan tenggat sewa makam secara otomatis.</small>
                    </div>

                    <div class="col-12">
                        <div class="border rounded-3 bg-light p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="small text-muted">No Makam</div>
                                    <div class="fw-semibold" id="renewal-no-makam">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted">Nama Jenazah</div>
                                    <div class="fw-semibold" id="renewal-nama-jenazah">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted">NIK Jenazah</div>
                                    <div class="fw-semibold" id="renewal-nik-jenazah">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted">Tempat Lahir</div>
                                    <div class="fw-semibold" id="renewal-tempat-lahir">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted">Tanggal Lahir</div>
                                    <div class="fw-semibold" id="renewal-tanggal-lahir">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted">Tanggal Wafat</div>
                                    <div class="fw-semibold" id="renewal-tanggal-wafat">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted">Jenis Kelamin</div>
                                    <div class="fw-semibold" id="renewal-jenis-kelamin">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted">Agama</div>
                                    <div class="fw-semibold" id="renewal-agama">-</div>
                                </div>
                                <div class="col-12">
                                    <div class="small text-muted">Alamat Jenazah</div>
                                    <div class="fw-semibold" id="renewal-alamat">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted">Blok Makam</div>
                                    <div class="fw-semibold" id="renewal-blok">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted">Zona Makam</div>
                                    <div class="fw-semibold" id="renewal-zona">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted">Tenggat Sewa</div>
                                    <div class="fw-semibold" id="renewal-tenggat">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted">Tahun Pemakaman</div>
                                    <div class="fw-semibold" id="renewal-tahun-pemakaman">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

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
                        <input type="file" name="scan_ktp_ahli_waris" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        @if($permohonan->scan_ktp_ahli_waris)
                            <small class="text-muted">Saat ini: <a href="{{ asset('storage/' . $permohonan->scan_ktp_ahli_waris) }}" target="_blank">Lihat file</a></small>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Scan KK</label>
                        <input type="file" name="scan_kk" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        @if($permohonan->scan_kk)
                            <small class="text-muted">Saat ini: <a href="{{ asset('storage/' . $permohonan->scan_kk) }}" target="_blank">Lihat file</a></small>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Surat Kematian</label>
                        <input type="file" name="surat_kematian" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
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

@if($isPerpanjangan)
    @push('scripts')
    <script>
        function fillRenewalFields(select) {
            const option = select.options[select.selectedIndex];

            const fields = {
                'renewal-no-makam': option?.dataset.noMakam || '-',
                'renewal-nama-jenazah': option?.dataset.namaJenazah || '-',
                'renewal-nik-jenazah': option?.dataset.nikJenazah || '-',
                'renewal-tempat-lahir': option?.dataset.tempatLahir || '-',
                'renewal-tanggal-lahir': formatRenewalDate(option?.dataset.tanggalLahir || ''),
                'renewal-tanggal-wafat': formatRenewalDate(option?.dataset.tanggalWafat || ''),
                'renewal-jenis-kelamin': option?.dataset.jenisKelamin || '-',
                'renewal-agama': option?.dataset.agama || '-',
                'renewal-alamat': option?.dataset.alamat || '-',
                'renewal-blok': option?.dataset.blok || '-',
                'renewal-zona': option?.dataset.zona || '-',
                'renewal-tenggat': formatRenewalDate(option?.dataset.tenggat || ''),
                'renewal-tahun-pemakaman': option?.dataset.tahunPemakaman || '-',
            };

            Object.entries(fields).forEach(([id, value]) => {
                const element = document.getElementById(id);
                if (element) {
                    element.textContent = value || '-';
                }
            });
        }

        function formatRenewalDate(value) {
            if (! value) {
                return '-';
            }

            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return value;
            }

            return date.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const renewalSelect = document.getElementById('renewal-jenazah');
            if (renewalSelect) {
                fillRenewalFields(renewalSelect);
            }
        });
    </script>
    @endpush
@endif
@endsection
