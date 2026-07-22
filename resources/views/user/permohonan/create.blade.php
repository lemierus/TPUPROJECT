@extends('admin.layouts.app')

@section('title', 'Ajukan Permohonan')

@section('content')
@php
    $jenis = old('jenis_permohonan', $selectedJenis ?? \App\Models\Permohonan::JENIS_MAKAM_BARU);
    $isPerpanjangan = $jenis === \App\Models\Permohonan::JENIS_PERPANJANGAN;
    $isDarurat = $jenis === \App\Models\Permohonan::JENIS_DARURAT;
@endphp

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Permohonan Ahli Waris</h4>
            <p class="text-muted mb-0">TPU tujuan: {{ $tpu }}</p>
            @if($assignedPetugas)
                <p class="text-muted mb-0">Petugas TPU: <strong>{{ $assignedPetugas->name }}</strong></p>
            @endif
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-2 border-dark shadow-sm mb-4">
            <i class="bi bi-exclamation-circle-fill me-2"></i>{{ $errors->first() }}
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <small class="text-muted d-block mb-3">
                <span class="text-danger">*</span> menandakan data wajib diisi
            </small>

            <form action="{{ route('user.permohonan.store') }}" method="POST" enctype="multipart/form-data" id="permohonan-form">
                @csrf
                <input type="hidden" name="tpu" value="{{ $tpu }}">

                <div class="mb-4">
                    <label class="form-label fw-semibold d-block">Pilih Jenis Permohonan</label>
                    <div class="d-flex flex-wrap gap-3">
                        <label class="form-check form-check-inline border rounded-3 px-3 py-2">
                            <input class="form-check-input" type="radio" name="jenis_permohonan" value="makam_baru" @checked($jenis === 'makam_baru')>
                            <span class="form-check-label">Reguler / Makam Baru</span>
                        </label>
                        <label class="form-check form-check-inline border rounded-3 px-3 py-2">
                            <input class="form-check-input" type="radio" name="jenis_permohonan" value="perpanjangan" @checked($jenis === 'perpanjangan')>
                            <span class="form-check-label">Perpanjangan Makam</span>
                        </label>
                        <label class="form-check form-check-inline border rounded-3 px-3 py-2 border-danger">
                            <input class="form-check-input" type="radio" name="jenis_permohonan" value="darurat" @checked($jenis === 'darurat')>
                            <span class="form-check-label text-danger fw-semibold">Darurat / Pemakaman Segera</span>
                        </label>
                    </div>
                </div>

                <div id="darurat-alert" class="alert alert-danger border-2 border-dark shadow-sm mb-4" style="{{ $isDarurat ? '' : 'display:none;' }}">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Permohonan Darurat:</strong> gunakan fitur ini ketika jenazah harus segera dimakamkan dan administrasi lengkap belum dapat disiapkan saat ini.
                </div>

                <div id="section-perpanjangan" style="{{ $isPerpanjangan ? '' : 'display:none;' }}">
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold">Data Perpanjangan Makam</h6>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Pilih Nama Jenazah <span class="text-danger">*</span></label>
                            <select name="jenazah_id" class="form-select">
                                <option value="">Pilih jenazah</option>
                                @foreach($perpanjanganJenazahs as $item)
                                    <option
                                        value="{{ $item->id }}"
                                        @selected((string) old('jenazah_id', $selectedRenewalJenazahId ?? '') === (string) $item->id)
                                    >
                                        {{ $item->nama ?? '-' }}
                                        @if($item->makam)
                                            - {{ $item->makam->kode_makam }}
                                        @endif
                                        @if($item->makam && $item->makam->jumlahJenazah() > 1)
                                            (tumpang sari - {{ $item->makam->jumlahJenazah() }} jenazah)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-2">
                                Untuk makam tumpang sari, perpanjangan dilakukan atas nama jenazah yang paling terakhir dimakamkan pada makam tersebut.
                            </small>
                            @if($perpanjanganJenazahs->isEmpty())
                                <small class="text-muted d-block mt-1">
                                    Tidak ditemukan data jenazah yang berhak diperpanjang pada TPU ini.
                                </small>
                            @endif
                        </div>
                    </div>
                </div>

                <div id="section-data-jenazah" style="{{ $isPerpanjangan ? 'display:none;' : '' }}">
                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="fw-bold">Data Jenazah</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Jenazah <span class="text-danger">*</span></label>
                            <input type="text" name="nama_jenazah" class="form-control" value="{{ old('nama_jenazah') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                NIK Jenazah
                                <span class="text-danger req-makam-baru" style="{{ $isDarurat ? 'display:none;' : '' }}">*</span>
                            </label>
                            <input type="text" name="nik_jenazah" class="form-control" value="{{ old('nik_jenazah') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="">Pilih</option>
                                <option value="Laki-laki" @selected(old('jenis_kelamin') === 'Laki-laki')>Laki-laki</option>
                                <option value="Perempuan" @selected(old('jenis_kelamin') === 'Perempuan')>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Agama <span class="text-danger">*</span></label>
                            <select name="agama" class="form-select">
                                <option value="">Pilih</option>
                                @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agama)
                                    <option value="{{ $agama }}" @selected(old('agama') === $agama)>{{ $agama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Meninggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_wafat" class="form-control" value="{{ old('tanggal_wafat') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Tempat Lahir
                                <span class="text-danger req-makam-baru" style="{{ $isDarurat ? 'display:none;' : '' }}">*</span>
                            </label>
                            <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Tanggal Lahir
                                <span class="text-danger req-makam-baru" style="{{ $isDarurat ? 'display:none;' : '' }}">*</span>
                            </label>
                            <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">
                                Alamat
                                <span class="text-danger req-makam-baru" style="{{ $isDarurat ? 'display:none;' : '' }}">*</span>
                            </label>
                            <textarea name="alamat" class="form-control" rows="3">{{ old('alamat') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <h6 class="fw-bold">Data Ahli Waris</h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nama Ahli Waris <span class="text-danger">*</span></label>
                        <input type="text" name="nama_ahli_waris" class="form-control" value="{{ old('nama_ahli_waris', auth()->user()->name) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nomor HP Ahli Waris <span class="text-danger">*</span></label>
                        <input type="text" name="no_hp_ahli_waris" class="form-control" value="{{ old('no_hp_ahli_waris', auth()->user()->no_hp) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hubungan dengan Jenazah <span class="text-danger">*</span></label>
                        <input type="text" name="hubungan_keluarga" class="form-control" value="{{ old('hubungan_keluarga') }}">
                    </div>

                    <!-- {{-- Biaya Sewa: makam_baru / darurat pakai daftar tetap --}}
                    <div class="col-md-4" id="biaya-wrapper" style="{{ $isPerpanjangan ? '' : 'display:none;' }}">
                        <label class="form-label">
                            Biaya Sewa Perpanjangan
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="biaya"
                            class="form-select"
                            id="biaya"
                            @if(! $isPerpanjangan) disabled @endif>

                            <option value="">Pilih</option>

                            @foreach($tpuBiayaSewas ?? [] as $item)
                                <option
                                    value="{{ $item->label }}"
                                    @selected(old('biaya') === $item->label)>
                                    {{ $item->label }} — Rp {{ number_format($item->harga,0,',','.') }}
                                </option>
                            @endforeach
                        </select>

                        @if(($tpuBiayaSewas ?? collect())->isEmpty())
                            <small class="text-muted">
                                Belum ada biaya sewa yang ditetapkan.
                            </small>
                        @endif
                    </div> -->

                    <div class="col-12" id="biaya-retribusi-section" style="{{ $isPerpanjangan ? '' : 'display:none;' }}">
                        <div class="border rounded-3 p-3 bg-light">
                            <h6 class="fw-bold mb-3">Biaya Retribusi Perpanjangan Makam</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Pilih Biaya Retribusi <span class="text-danger">*</span></label>
                                    <select name="biaya_retribusi_id" class="form-select" id="biaya-retribusi-select" @if(! $isPerpanjangan) disabled @endif>
                                        <option value="">Pilih biaya retribusi</option>
                                        @foreach($biayaRetribusis ?? [] as $item)
                                            <option value="{{ $item->id }}"
                                                    data-nominal="{{ $item->nominal }}"
                                                    data-rekening="{{ $item->nomor_rekening }}"
                                                    data-bank="{{ $item->nama_bank }}"
                                                    data-atas-nama="{{ $item->atas_nama_rekening }}"
                                                    @selected((string) old('biaya_retribusi_id') === (string) $item->id)>
                                                {{ $item->nama_biaya }} - Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nominal</label>
                                    <input type="text" class="form-control" id="biaya-retribusi-nominal" value="-" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nomor Rekening Tujuan</label>
                                    <input type="text" class="form-control" id="biaya-retribusi-rekening" value="-" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nama Bank</label>
                                    <input type="text" class="form-control" id="biaya-retribusi-bank" value="-" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Atas Nama Rekening</label>
                                    <input type="text" class="form-control" id="biaya-retribusi-atas-nama" value="-" readonly>
                                </div>
                                <div class="col-md-6" id="bukti-transfer-wrapper" style="display:none;">
                                    <label class="form-label">Upload Bukti Transfer <span class="text-danger">*</span></label>
                                    <input type="file" name="bukti_transfer" id="bukti-transfer-input" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                    <small class="text-muted d-block mt-1">Wajib diisi jika nominal biaya retribusi lebih dari Rp0.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                    <label class="form-label">
                        Keterangan / Catatan <span class="text-danger">*</span>
                    </label>

                    
                    <textarea
                        name="catatan"
                        class="form-control"
                        rows="3"
                        placeholder="Masukkan keterangan atau catatan tambahan terkait permohonan...">{{ old('catatan') }}</textarea>

                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle text-primary me-1"></i>
                        <strong>Informasi:</strong> Apabila Anda ingin mengajukan <strong>pemakaman tumpang sari</strong>,
                        silakan tuliskan pada kolom keterangan dengan kalimat
                        <strong>"Ingin melakukan pemakaman tumpang sari"</strong>
                        dan sebutkan
                        <strong>nama jenazah</strong>  
                        yang sudah dikubur di TPU terkait. Permohonan akan diproses sesuai dengan ketentuan yang berlaku.
                    </small>
                </div>
                </div>

                <div id="section-dokumen" class="row g-3 mt-1" style="{{ $isPerpanjangan ? 'display:none;' : '' }}">
                    <div class="col-12">
                        <h6 class="fw-bold">Upload Dokumen (maks 2MB)</h6>
                        @if($isDarurat)
                            <small class="text-muted d-block">Opsional untuk permohonan darurat — bisa dilengkapi menyusul.</small>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">
                            Scan KTP Ahli Waris
                            <span class="text-danger req-makam-baru" style="{{ $isDarurat ? 'display:none;' : '' }}">*</span>
                        </label>
                        <input type="file" name="scan_ktp_ahli_waris" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">
                            Scan KK
                            <span class="text-danger req-makam-baru" style="{{ $isDarurat ? 'display:none;' : '' }}">*</span>
                        </label>
                        <input type="file" name="scan_kk" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">
                            Surat Kematian
                            <span class="text-danger req-makam-baru" style="{{ $isDarurat ? 'display:none;' : '' }}">*</span>
                        </label>
                        <input type="file" name="surat_kematian" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn" style="background:#1E3E62;color:#fff;">
                        <i class="bi bi-send me-1"></i> Kirim Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('input[name="jenis_permohonan"]');
    const perpanjangan = document.getElementById('section-perpanjangan');
    const dataJenazah = document.getElementById('section-data-jenazah');
    const daruratAlert = document.getElementById('darurat-alert');
    const dokumen = document.getElementById('section-dokumen');
    const reqMakamBaru = document.querySelectorAll('.req-makam-baru');
    const reqDarurat = document.querySelectorAll('.req-darurat');
    const biayaRetribusiSection = document.getElementById('biaya-retribusi-section');
    const biayaRetribusiSelect = document.getElementById('biaya-retribusi-select');
    const biayaRetribusiNominal = document.getElementById('biaya-retribusi-nominal');
    const biayaRetribusiRekening = document.getElementById('biaya-retribusi-rekening');
    const biayaRetribusiBank = document.getElementById('biaya-retribusi-bank');
    const biayaRetribusiAtasNama = document.getElementById('biaya-retribusi-atas-nama');
    const buktiTransferWrapper = document.getElementById('bukti-transfer-wrapper');
    const buktiTransferInput = document.getElementById('bukti-transfer-input');

    function formatRupiah(nominal) {
        const value = Number(nominal || 0);
        return 'Rp ' + value.toLocaleString('id-ID');
    }

    function refreshBiayaRetribusi() {
        if (!biayaRetribusiSelect) {
            return;
        }

        const selectedOption = biayaRetribusiSelect.options[biayaRetribusiSelect.selectedIndex];
        const nominal = Number(selectedOption?.dataset?.nominal || 0);

        biayaRetribusiNominal.value = selectedOption?.value ? formatRupiah(nominal) : '-';
        biayaRetribusiRekening.value = selectedOption?.value ? (selectedOption.dataset.rekening || '-') : '-';
        biayaRetribusiBank.value = selectedOption?.value ? (selectedOption.dataset.bank || '-') : '-';
        biayaRetribusiAtasNama.value = selectedOption?.value ? (selectedOption.dataset.atasNama || '-') : '-';

        const wajibBukti = selectedOption?.value && nominal > 0;
        buktiTransferWrapper.style.display = wajibBukti ? '' : 'none';
        if (buktiTransferInput) {
            buktiTransferInput.required = !!wajibBukti;
        }
    }

    function refreshSection() {
        const selected = document.querySelector('input[name="jenis_permohonan"]:checked')?.value || 'makam_baru';
        const isPerpanjangan = selected === 'perpanjangan';

        perpanjangan.style.display = isPerpanjangan ? '' : 'none';
        dataJenazah.style.display = isPerpanjangan ? 'none' : '';
        daruratAlert.style.display = selected === 'darurat' ? '' : 'none';
        dokumen.style.display = isPerpanjangan ? 'none' : '';

        if (biayaRetribusiSection) {
            biayaRetribusiSection.style.display = isPerpanjangan ? '' : 'none';
        }
        if (biayaRetribusiSelect) {
            biayaRetribusiSelect.disabled = !isPerpanjangan;
        }
        if (!isPerpanjangan && buktiTransferInput) {
            buktiTransferInput.required = false;
        }

        reqMakamBaru.forEach((el) => {
            el.style.display = selected === 'makam_baru' ? '' : 'none';
        });
        reqDarurat.forEach((el) => {
            el.style.display = selected === 'darurat' ? '' : 'none';
        });

        refreshBiayaRetribusi();
    }

    radios.forEach((radio) => radio.addEventListener('change', refreshSection));
    if (biayaRetribusiSelect) {
        biayaRetribusiSelect.addEventListener('change', refreshBiayaRetribusi);
    }
    refreshSection();
});
</script>
@endpush
@endsection
