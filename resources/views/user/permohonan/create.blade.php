@extends('admin.layouts.app')

@section('title', 'Ajukan Permohonan')

@section('content')
@php
    $formatRenewalDate = static fn ($value) => filled($value)
        ? \Illuminate\Support\Carbon::parse($value)->format('d-m-Y')
        : '';
    $jenis = old('jenis_permohonan', $selectedJenis ?? 'makam_baru');
    $selectedRenewalJenazahId = old('jenazah_id', $selectedRenewalJenazahId ?? null);
    $selectedSourcePermohonanId = old('source_permohonan_id', $selectedSourcePermohonanId ?? null);
    $isPerpanjangan = $jenis === 'perpanjangan';
    $fixedRenewalSource = $renewalSource ?? null;
    $fixedRenewalJenazah = $fixedRenewalSource?->jenazah;
    $fixedRenewalMakam = $fixedRenewalJenazah?->makam ?? $fixedRenewalSource?->makam;
    $fixedRenewalTanggalLahir = $formatRenewalDate($fixedRenewalSource?->tanggal_lahir ?? $fixedRenewalSource?->jenazah?->tanggal_lahir);
    $fixedRenewalTanggalWafat = $formatRenewalDate($fixedRenewalSource?->tanggal_wafat ?? $fixedRenewalSource?->jenazah?->tanggal_wafat);
    $fixedRenewalTenggat = $fixedRenewalSource?->renewalDueAt()?->format('d-m-Y');
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
            <form id="permohonan-form" action="{{ route('user.permohonan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="tpu" value="{{ $tpu }}">
                <input type="hidden" name="jenis_permohonan" value="{{ $jenis }}">
                @if($isPerpanjangan && ($fixedRenewalSource?->id || $selectedSourcePermohonanId))
                    <input type="hidden" name="source_permohonan_id" value="{{ $fixedRenewalSource?->id ?? $selectedSourcePermohonanId }}">
                @endif

                <div class="alert {{ $isPerpanjangan ? 'alert-primary' : 'alert-secondary' }} border-2 border-dark shadow-sm mb-4">
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi {{ $isPerpanjangan ? 'bi-arrow-repeat' : 'bi-plus-circle' }} fs-4"></i>
                        <div>
                            <div class="fw-bold mb-1">
                                {{ $isPerpanjangan ? 'Form Perpanjangan Sewa Makam' : 'Form Pembuatan Makam Baru' }}
                            </div>
                            <div class="mb-0">
                                {{ $isPerpanjangan
                                    ? 'Data jenazah dan data makam diambil otomatis dari ringkasan data pemakaman yang sudah disetujui.'
                                    : 'Lengkapi data jenazah, ahli waris, dan dokumen pendukung untuk pengajuan makam baru.' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div id="section-makam-baru" class="jenis-section" style="{{ $isPerpanjangan ? 'display:none;' : '' }}">
                    <div class="row g-3 mt-3">
                        <div class="col-12">
                            <h6 class="fw-bold mb-0">Data Jenazah</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">NIK Jenazah <span class="text-danger">*</span></label>
                            <input type="text" name="nik_jenazah" class="form-control" value="{{ old('nik_jenazah') }}" @unless($isPerpanjangan) required @endunless>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nama Jenazah <span class="text-danger">*</span></label>
                            <input type="text" name="nama_jenazah" class="form-control" value="{{ old('nama_jenazah') }}" @unless($isPerpanjangan) required @endunless>
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

                <div id="section-perpanjangan" class="jenis-section" style="{{ $isPerpanjangan ? '' : 'display:none;' }}">
                    <div class="row g-3 mt-3">
                        <div class="col-12">
                            <h6 class="fw-bold mb-0">Data Perpanjangan Sewa Makam</h6>
                        </div>

                        <div class="col-12">
                            @if($fixedRenewalSource)
                                <div class="alert alert-info border-2 border-dark mb-0">
                                    Data perpanjangan diambil dari permohonan yang sudah disetujui pada TPU {{ $tpu }}.
                                </div>
                                <input type="hidden" name="jenazah_id" value="{{ $fixedRenewalSource->jenazah_id }}">
                            @elseif(($perpanjanganJenazahs ?? collect())->isEmpty())
                                <div class="alert alert-warning border-2 border-dark mb-0">
                                    Tidak ada data jenazah yang bisa diperpanjang untuk TPU ini.
                                </div>
                            @else
                                <label class="form-label">Pilih Nama Jenazah</label>
                                <select name="jenazah_id" id="renewal-jenazah" class="form-select" onchange="fillRenewalFields(this)" required>
                                    <option value="">Pilih jenazah</option>
                                    @foreach($perpanjanganJenazahs as $item)
                                        @php
                                            $itemTanggalLahir = $formatRenewalDate($item->tanggal_lahir ?? $item->jenazah?->tanggal_lahir);
                                            $itemTanggalWafat = $formatRenewalDate($item->tanggal_wafat ?? $item->jenazah?->tanggal_wafat);
                                            $itemTenggat = $formatRenewalDate($item->renewalDueAt());
                                        @endphp
                                        <option value="{{ $item->jenazah_id }}"
                                            @selected((string) $selectedRenewalJenazahId === (string) $item->jenazah_id)
                                            data-nama-jenazah="{{ $item->nama_jenazah ?? $item->jenazah?->nama ?? '' }}"
                                            data-nik-jenazah="{{ $item->nik_jenazah ?? $item->jenazah?->nik ?? '' }}"
                                            data-tempat-lahir="{{ $item->tempat_lahir ?? $item->jenazah?->tempat_lahir ?? '' }}"
                                            data-tanggal-lahir="{{ $itemTanggalLahir }}"
                                            data-tanggal-wafat="{{ $itemTanggalWafat }}"
                                            data-jenis-kelamin="{{ $item->jenis_kelamin ?? $item->jenazah?->jenis_kelamin ?? '' }}"
                                            data-agama="{{ $item->agama ?? $item->jenazah?->agama ?? '' }}"
                                            data-alamat="{{ $item->alamat ?? $item->jenazah?->alamat ?? '' }}"
                                            data-kode-makam="{{ $item->makam?->kode_makam ?? '' }}"
                                            data-blok="{{ $item->makam?->blok ?? '' }}"
                                            data-zona="{{ $item->makam?->zona ?? '' }}"
                                            data-no-makam="{{ $item->makam?->nomor ?? '' }}"
                                            data-keterangan-makam="{{ $item->makam?->keterangan ?? '' }}"
                                            data-tenggat="{{ $itemTenggat }}">
                                            {{ $item->nama_jenazah ?? $item->jenazah?->nama ?? '-' }}
                                            @if($item->makam)
                                                - {{ $item->makam->kode_makam }} / {{ $item->makam->blok ?? '-' }} / {{ $item->makam->zona ?? '-' }} / No {{ $item->makam->nomor ?? '-' }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">Pilih jenazah yang ingin diperpanjang. Data makam akan ditampilkan otomatis.</small>
                            @endif
                        </div>

                        <div class="col-12">
                            <div class="border rounded-3 bg-light p-3">
                                <div class="row g-3">
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
                                        <div class="small text-muted">Kode Makam</div>
                                        <div class="fw-semibold" id="renewal-kode-makam">-</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted">Blok / Zona</div>
                                        <div class="fw-semibold" id="renewal-blok-zona">-</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted">Nomor Makam</div>
                                        <div class="fw-semibold" id="renewal-no-makam">-</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted">Keterangan Makam</div>
                                        <div class="fw-semibold" id="renewal-keterangan-makam">-</div>
                                    </div>
                                    <div class="col-12">
                                        <div class="small text-muted">Tenggat Sewa</div>
                                        <div class="fw-semibold" id="renewal-tenggat">-</div>
                                    </div>
                                </div>
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

                @if(! $isPerpanjangan)
                    <div class="row g-3 mt-1">
                        <div class="col-12">
                            <h6 class="fw-bold mb-0">Upload Dokumen</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Scan KTP Ahli Waris <span class="text-danger">*</span></label>
                            <input type="file" name="scan_ktp_ahli_waris" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Scan KK <span class="text-danger">*</span></label>
                            <input type="file" name="scan_kk" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Surat Kematian <span class="text-danger">*</span></label>
                            <input type="file" name="surat_kematian" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                    </div>
                @endif

                <div class="d-flex justify-content-end mt-4 position-sticky" style="bottom: 1rem; z-index: 20;">
                    <button
                        type="submit"
                        form="permohonan-form"
                        class="btn shadow-sm"
                        style="background-color:#1E3E62;color:white; position:relative; z-index:21;"
                    >
                        <i class="bi bi-send"></i> Kirim Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function fillRenewalFields(select) {
        if (!select) {
            return;
        }

        const option = select.options[select.selectedIndex];

        const setValue = (id, value) => {
            const el = document.getElementById(id);
            if (el) {
                el.textContent = value || '-';
            }
        };

        if (!option || !option.value) {
            [
                'renewal-nama-jenazah',
                'renewal-nik-jenazah',
                'renewal-tempat-lahir',
                'renewal-tanggal-lahir',
                'renewal-tanggal-wafat',
                'renewal-jenis-kelamin',
                'renewal-agama',
                'renewal-alamat',
                'renewal-kode-makam',
                'renewal-blok-zona',
                'renewal-no-makam',
                'renewal-keterangan-makam',
                'renewal-tenggat',
            ].forEach((id) => setValue(id, '-'));
            return;
        }

        const blok = option.dataset.blok || '';
        const zona = option.dataset.zona || '';

        setValue('renewal-nama-jenazah', option.dataset.namaJenazah);
        setValue('renewal-nik-jenazah', option.dataset.nikJenazah);
        setValue('renewal-tempat-lahir', option.dataset.tempatLahir);
        setValue('renewal-tanggal-lahir', option.dataset.tanggalLahir);
        setValue('renewal-tanggal-wafat', option.dataset.tanggalWafat);
        setValue('renewal-jenis-kelamin', option.dataset.jenisKelamin);
        setValue('renewal-agama', option.dataset.agama);
        setValue('renewal-alamat', option.dataset.alamat);
        setValue('renewal-kode-makam', option.dataset.kodeMakam);
        setValue('renewal-blok-zona', [blok, zona].filter(Boolean).join(' / ') || '-');
        setValue('renewal-no-makam', option.dataset.noMakam);
        setValue('renewal-keterangan-makam', option.dataset.keteranganMakam);
        setValue('renewal-tenggat', option.dataset.tenggat);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const renewalSelect = document.getElementById('renewal-jenazah');
        const hasFixedSource = @json($fixedRenewalSource !== null);

        if (renewalSelect && renewalSelect.value) {
            renewalSelect.required = true;
            fillRenewalFields(renewalSelect);
        } else if (hasFixedSource) {
            const fixedData = {
                namaJenazah: @json($fixedRenewalSource?->nama_jenazah ?? $fixedRenewalSource?->jenazah?->nama ?? ''),
                nikJenazah: @json($fixedRenewalSource?->nik_jenazah ?? $fixedRenewalSource?->jenazah?->nik ?? ''),
                tempatLahir: @json($fixedRenewalSource?->tempat_lahir ?? $fixedRenewalSource?->jenazah?->tempat_lahir ?? ''),
                tanggalLahir: @json($fixedRenewalTanggalLahir),
                tanggalWafat: @json($fixedRenewalTanggalWafat),
                jenisKelamin: @json($fixedRenewalSource?->jenis_kelamin ?? $fixedRenewalSource?->jenazah?->jenis_kelamin ?? ''),
                agama: @json($fixedRenewalSource?->agama ?? $fixedRenewalSource?->jenazah?->agama ?? ''),
                alamat: @json($fixedRenewalSource?->alamat ?? $fixedRenewalSource?->jenazah?->alamat ?? ''),
                kodeMakam: @json($fixedRenewalMakam?->kode_makam ?? ''),
                blok: @json($fixedRenewalMakam?->blok ?? ''),
                zona: @json($fixedRenewalMakam?->zona ?? ''),
                noMakam: @json($fixedRenewalMakam?->nomor ?? ''),
                keteranganMakam: @json($fixedRenewalMakam?->keterangan ?? ''),
                tenggat: @json($fixedRenewalTenggat),
            };

            const setValue = (id, value) => {
                const el = document.getElementById(id);
                if (el) {
                    el.textContent = value || '-';
                }
            };

            setValue('renewal-nama-jenazah', fixedData.namaJenazah);
            setValue('renewal-nik-jenazah', fixedData.nikJenazah);
            setValue('renewal-tempat-lahir', fixedData.tempatLahir);
            setValue('renewal-tanggal-lahir', fixedData.tanggalLahir);
            setValue('renewal-tanggal-wafat', fixedData.tanggalWafat);
            setValue('renewal-jenis-kelamin', fixedData.jenisKelamin);
            setValue('renewal-agama', fixedData.agama);
            setValue('renewal-alamat', fixedData.alamat);
            setValue('renewal-kode-makam', fixedData.kodeMakam);
            setValue('renewal-blok-zona', [fixedData.blok, fixedData.zona].filter(Boolean).join(' / ') || '-');
            setValue('renewal-no-makam', fixedData.noMakam);
            setValue('renewal-keterangan-makam', fixedData.keteranganMakam);
            setValue('renewal-tenggat', fixedData.tenggat);
        }
    });
</script>
@endpush
@endsection
