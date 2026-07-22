@extends('admin.layouts.app')

@section('title', 'Detail Permohonan')

@push('styles')
<style>
    .detail-header {
        background: #1E3E62;
        color: #fff;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
    }

    .detail-section {
        border: 2px solid #111827;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        background: #fff;
    }

    .detail-section-title {
        font-weight: 800;
        color: #101828;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .detail-label {
        font-weight: 600;
        color: #475467;
        font-size: .85rem;
        text-transform: uppercase;
        letter-spacing: .02em;
        margin-bottom: .25rem;
    }

    .detail-value {
        color: #101828;
        font-weight: 500;
        margin-bottom: 1rem;
        font-size: 1rem;
    }

    .detail-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .detail-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .5rem 1rem;
        border-radius: 999px;
        font-weight: 700;
        font-size: .85rem;
    }

    .detail-badge-success {
        background: #e8fff2;
        color: #027a48;
    }

    .detail-badge-warning {
        background: #fef3c7;
        color: #b45309;
    }

    .detail-badge-danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .detail-badge-primary {
        background: #ecf2ff;
        color: #1E3E62;
    }

    .doc-link {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .75rem 1rem;
        border: 2px solid #111827;
        border-radius: 10px;
        text-decoration: none;
        color: #1E3E62;
        font-weight: 600;
        transition: all .2s ease;
    }

    .doc-link:hover {
        background: #1E3E62;
        color: #fff;
        border-color: #1E3E62;
    }

    .whatsapp-link {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .45rem .8rem;
        border: 2px solid #198754;
        border-radius: 999px;
        text-decoration: none;
        color: #198754;
        font-weight: 600;
        font-size: .82rem;
        line-height: 1;
        transition: all .2s ease;
        background: #fff;
    }

    .whatsapp-link:hover {
        background: #198754;
        color: #fff;
        border-color: #198754;
    }

    .whatsapp-link-inline {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        margin-left: .65rem;
        padding: .45rem .8rem;
        border: 2px solid #198754;
        border-radius: 999px;
        text-decoration: none;
        color: #198754;
        font-weight: 600;
        font-size: .82rem;
        line-height: 1;
        background: #fff;
        transition: all .2s ease;
        vertical-align: middle;
    }

    .whatsapp-link-inline:hover {
        background: #198754;
        color: #fff;
        border-color: #198754;
        text-decoration: none;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .action-btn {
        flex: 1;
        padding: .75rem 1rem;
        border: 2px solid #111827;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s ease;
        font-size: 1rem;
    }

    .action-btn-approve {
        background: #027a48;
        color: #fff;
        border-color: #027a48;
    }

    .action-btn-approve:hover {
        background: #06654f;
        border-color: #06654f;
    }

    .action-btn-reject {
        background: #dc2626;
        color: #fff;
        border-color: #dc2626;
    }

    .action-btn-reject:hover {
        background: #b91c1c;
        border-color: #b91c1c;
    }

    .action-btn-back {
        background: #fff;
        color: #101828;
        border-color: #111827;
    }

    .action-btn-back:hover {
        background: #f8fafc;
    }

    .modal-custom {
        border: 2px solid #111827 !important;
        border-radius: 16px !important;
    }

    .modal-custom .modal-header {
        background: #1E3E62;
        color: #fff;
        border-bottom: 2px solid #111827;
        border-radius: 14px 14px 0 0;
    }

    .modal-custom .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .form-control-custom {
        border: 2px solid #d0d5dd;
        border-radius: 10px;
        padding: .75rem 1rem;
    }

    .form-control-custom:focus {
        border-color: #1E3E62;
        box-shadow: 0 0 0 3px rgba(30, 62, 98, 0.1);
    }

    /* Toggle tipe pemakaman (dipakai di modal approve DAN di form selesaikan
       pemakaman darurat) */
    .tipe-pemakaman-toggle .form-check {
        border: 2px solid #d0d5dd;
        border-radius: 10px;
        padding: .6rem 1rem .6rem 2.2rem;
        flex: 1;
        cursor: pointer;
        transition: all .2s ease;
    }

    .tipe-pemakaman-toggle .form-check:has(.form-check-input:checked) {
        border-color: #1E3E62;
        background: #ecf2ff;
    }

    @media (max-width: 767.98px) {
        .detail-section {
            padding: 1rem;
        }

        .action-buttons {
            flex-direction: column;
        }

        .action-btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success border-2 border-dark shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{!! session('success') !!}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-2 border-dark shadow-sm mb-4">
            <i class="bi bi-exclamation-circle-fill me-2"></i>{{ $errors->first() }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-2 border-dark shadow-sm mb-4">
            <i class="bi bi-exclamation-circle-fill me-2"></i>{!! session('error') !!}
        </div>
    @endif

    <div class="detail-header d-flex justify-content-between align-items-start gap-3 flex-wrap">
        <div>
            <h3 class="mb-2">Detail Permohonan</h3>
            <p class="mb-0 opacity-75">
                @if($permohonan->created_at)
                    {{ \Carbon\Carbon::parse($permohonan->created_at)->format('d F Y H:i') }}
                @else
                    -
                @endif
            </p>
        </div>
        <div class="text-lg-end">
            @php
                $status = strtolower($permohonan->status ?? '');
                $badgeClass = match($status) {
                    'disetujui' => 'detail-badge-success',
                    'ditolak' => 'detail-badge-danger',
                    'administrasi_belum_lengkap', 'perlu_perbaikan_dokumen' => 'detail-badge-danger',
                    'menunggu_verifikasi_dokumen', 'diproses_darurat', 'menunggu_konfirmasi' => 'detail-badge-warning',
                    'selesai' => 'detail-badge-success',
                    default => 'detail-badge-warning'
                };
                $statusLabel = $permohonan->statusLabel();
            @endphp
            <span class="detail-badge {{ $badgeClass }}">
                <i class="bi @if($status === 'disetujui') bi-check-circle @elseif($status === 'ditolak') bi-x-circle @else bi-hourglass-split @endif"></i>
                {{ $statusLabel }}
            </span>
        </div>
    </div>

    @if($permohonan->jenis_permohonan === 'makam_baru' && $permohonan->status === 'disetujui')
        <div class="alert {{ $permohonan->jenazah_id ? 'alert-info' : 'alert-warning' }} border-2 border-dark shadow-sm mb-4">
            <i class="bi {{ $permohonan->jenazah_id ? 'bi-info-circle-fill' : 'bi-exclamation-triangle-fill' }} me-2"></i>
            @if($permohonan->jenazah_id)
                <strong>Data Jenazah Tersimpan:</strong> Data jenazah sudah disimpan ke halaman data jenazah.
            @else
                <strong>Data Jenazah Belum Tersimpan:</strong> Data jenazah belum disimpan. Pastikan NIK dan nama jenazah sudah diisi sebelum menyetujui permohonan ini.
            @endif
        </div>
    @endif

    @php
        $renewalDueAt = $permohonan->renewalDueAt();
        $renewalLevel = $permohonan->renewalAlertLevel();
        $renewalTextClass = $renewalLevel === 'expired'
            ? 'text-danger'
            : ($renewalLevel === 'soon' ? 'text-warning' : 'text-success');
        $linkedJenazah = $permohonan->jenazah;
        $linkedMakam = $permohonan->makam ?? $linkedJenazah?->makam;
        $displayMakamKode = $permohonan->kode_makam ?? $linkedMakam?->kode_makam ?? '-';
        $displayMakamBlokZona = $permohonan->blok_zona_makam ?? trim(implode(' / ', array_filter([$linkedMakam?->blok, $linkedMakam?->zona])), ' /');
        if (empty($displayMakamBlokZona)) {
            $displayMakamBlokZona = '-';
        }
        $displayMakamNomor = $permohonan->no_makam ?? $linkedMakam?->nomor ?? '-';
        $displayMakamKeterangan = $permohonan->keterangan ?? $linkedMakam?->keterangan ?? '-';

        // Jenazah lain yang berbagi makam yang sama (relevan untuk tumpang sari).
        $otherJenazahsInMakam = $linkedMakam
            ? $linkedMakam->jenazahs()
                ->when($linkedJenazah, fn($q) => $q->where('id', '!=', $linkedJenazah->id))
                ->get(['id', 'nama', 'tanggal_wafat'])
            : collect();
    @endphp

    @if($renewalDueAt)
        @php
            $renewalClass = $renewalLevel === 'expired'
                ? 'alert-danger'
                : ($renewalLevel === 'soon' ? 'alert-warning' : 'alert-success');
            $renewalTitle = $renewalLevel === 'expired'
                ? 'Masa sewa makam sudah melewati batas 2 tahun'
                : ($renewalLevel === 'soon'
                    ? 'Masa sewa makam mendekati batas 2 tahun'
                    : 'Masa sewa makam masih aman');
        @endphp
        <div class="alert {{ $renewalClass }} border-2 border-dark shadow-sm mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2">
                <div>
                    <div class="fw-bold mb-1">
                        <i class="bi bi-calendar-event me-2"></i>{{ $renewalTitle }}
                    </div>
                    <div class="mb-0">
                        Tenggat sewa makam untuk permohonan ini adalah <strong>{{ $renewalDueAt->format('d-m-Y') }}</strong>.
                        @if($renewalLevel === 'expired')
                            Segera informasikan ke ahli waris agar melakukan perpanjangan.
                        @elseif($renewalLevel === 'soon')
                            Mohon ingatkan ahli waris agar menyiapkan perpanjangan sebelum batas berakhir.
                        @else
                            Tenggat masih jauh, cukup dipantau secara berkala.
                        @endif
                    </div>
                </div>
                <div class="text-md-end">
                    @if($renewalLevel === 'expired')
                        <span class="detail-badge detail-badge-danger">Lewat batas</span>
                        @if(! empty($renewalReminderWaUrl))
                            <div class="mt-2">
                                <a href="{{ $renewalReminderWaUrl }}" target="_blank" class="whatsapp-link">
                                    <i class="bi bi-whatsapp"></i>
                                    Kirim pengingat ke ahli waris
                                </a>
                            </div>
                        @endif
                    @elseif($renewalLevel === 'soon')
                        <span class="detail-badge detail-badge-warning">Mendekati batas</span>
                    @else
                        <span class="detail-badge detail-badge-success">Aman</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Jenis Permohonan -->
    <div class="detail-section">
        <div class="detail-section-title">
            <i class="bi bi-info-circle"></i>
            Informasi Permohonan
        </div>
        <div class="detail-row">
            <div>
                <div class="detail-label">Jenis Permohonan</div>
                <div class="detail-badge {{
                    $permohonan->jenis_permohonan === 'darurat'
                        ? 'detail-badge-danger'
                        : ($permohonan->jenis_permohonan === 'perpanjangan' ? 'detail-badge-primary' : 'detail-badge-success')
                }}">
                    {{
                        $permohonan->jenis_permohonan === 'darurat'
                            ? 'Permohonan Darurat'
                            : ($permohonan->jenis_permohonan === 'perpanjangan' ? 'Perpanjangan Makam' : 'Makam Baru')
                    }}
                </div>
            </div>
            <div>
                <div class="detail-label">TPU Tujuan</div>
                <div class="detail-value">{{ $permohonan->tpu }}</div>
            </div>
            <div>
                <div class="detail-label">Tanggal Pengajuan</div>
                <div class="detail-value">
                    @if($permohonan->created_at)
                        {{ \Carbon\Carbon::parse($permohonan->created_at)->format('d F Y') }}
                    @else
                        -
                    @endif
                </div>
            </div>
            @if($permohonan->tipe_pemakaman)
                <div>
                    <div class="detail-label">Tipe Pemakaman</div>
                    <div class="detail-badge {{ $permohonan->tipe_pemakaman === 'tumpang_sari' ? 'detail-badge-primary' : 'detail-badge-success' }}">
                        {{ $permohonan->tipe_pemakaman === 'tumpang_sari' ? 'Tumpang Sari' : 'Makam Baru' }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Data Jenazah / Makam -->
    <div class="detail-section">
        <div class="detail-section-title">
            <i class="bi bi-person"></i>
            Data Jenazah
        </div>
        <div class="detail-row">
            <div>
                <div class="detail-label">Nama</div>
                <div class="detail-value">{{ $permohonan->nama_jenazah ?? $linkedJenazah?->nama ?? '-' }}</div>
            </div>
            <div>
                <div class="detail-label">NIK</div>
                <div class="detail-value">{{ $permohonan->nik_jenazah ?? $linkedJenazah?->nik ?? '-' }}</div>
            </div>
            <div>
                <div class="detail-label">Jenis Kelamin</div>
                <div class="detail-value">{{ $permohonan->jenis_kelamin ?? $linkedJenazah?->jenis_kelamin ?? '-' }}</div>
            </div>
            <div>
                <div class="detail-label">Agama</div>
                <div class="detail-value">{{ $permohonan->agama ?? $linkedJenazah?->agama ?? '-' }}</div>
            </div>
            <div>
                <div class="detail-label">Tempat Lahir</div>
                <div class="detail-value">{{ $permohonan->tempat_lahir ?? $linkedJenazah?->tempat_lahir ?? '-' }}</div>
            </div>
            <div>
                <div class="detail-label">Tanggal Lahir</div>
                <div class="detail-value">
                    @if($permohonan->tanggal_lahir || $linkedJenazah?->tanggal_lahir)
                        {{ \Carbon\Carbon::parse($permohonan->tanggal_lahir ?? $linkedJenazah?->tanggal_lahir)->format('d F Y') }}
                    @else
                        -
                    @endif
                </div>
            </div>
            <div>
                <div class="detail-label">Tanggal Wafat</div>
                <div class="detail-value">
                    @if($permohonan->tanggal_wafat || $linkedJenazah?->tanggal_wafat)
                        {{ \Carbon\Carbon::parse($permohonan->tanggal_wafat ?? $linkedJenazah?->tanggal_wafat)->format('d F Y') }}
                    @else
                        -
                    @endif
                </div>
            </div>
            <div>
                <div class="detail-label">Tenggat Sewa Makam</div>
                <div class="detail-value">
                    @if($renewalDueAt)
                        <span class="{{ $renewalTextClass }}">{{ $renewalDueAt->format('d-m-Y') }}</span>
                        @if($renewalLevel === 'expired')
                            <span class="detail-badge detail-badge-danger ms-2">Lewat batas</span>
                        @elseif($renewalLevel === 'soon')
                            <span class="detail-badge detail-badge-warning ms-2">Mendekati batas</span>
                        @else
                            <span class="detail-badge detail-badge-success ms-2">Aman</span>
                        @endif
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="detail-section">
        <div class="detail-section-title">
            <i class="bi bi-tree"></i>
            Data Makam
        </div>
        <div class="detail-row">
            <div>
                <div class="detail-label">Kode Makam</div>
                <div class="detail-value">{{ $displayMakamKode ?? '-' }}</div>
            </div>
            <div>
                <div class="detail-label">Blok / Zona</div>
                <div class="detail-value">{{ $displayMakamBlokZona }}</div>
            </div>
            <div>
                <div class="detail-label">Nomor Makam</div>
                <div class="detail-value">{{ $displayMakamNomor }}</div>
            </div>
            <div>
                <div class="detail-label">Keterangan Makam</div>
                <div class="detail-value">{{ $displayMakamKeterangan }}</div>
            </div>
        </div>

        {{-- Daftar jenazah lain yang berbagi makam ini (tumpang sari) --}}
        @if($otherJenazahsInMakam->isNotEmpty())
            <hr class="my-3">
            <div class="detail-label mb-2">Jenazah Lain di Makam Ini (Tumpang Sari)</div>
            <ul class="mb-0">
                @foreach($otherJenazahsInMakam as $other)
                    <li>
                        {{ $other->nama ?? '-' }}
                        @if($other->tanggal_wafat)
                            &mdash; wafat {{ \Carbon\Carbon::parse($other->tanggal_wafat)->format('d F Y') }}
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <!-- Status Integrasi Data Jenazah -->
    @if($permohonan->jenis_permohonan === 'makam_baru')
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="bi bi-link-45deg"></i>
                Status Integrasi Data Jenazah
            </div>
            <div class="detail-row">
                <div>
                    <div class="detail-label">Status Penyimpanan Jenazah</div>
                    @if($permohonan->jenazah_id)
                        <p class="text-muted mt-2 mb-0">
                            Data jenazah sudah berhasil disimpan ke database data jenazah {{ $permohonan->tpu }}
                            <a href="{{ route('petugas.data-jenazah') }}" class="fw-semibold">Lihat di Data Jenazah →</a>
                        </p>
                    @else
                        <div class="detail-badge detail-badge-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Belum Tersimpan
                        </div>
                        <p class="text-muted mt-2 mb-0">
                            @if(empty($permohonan->nama_jenazah) || empty($permohonan->nik_jenazah))
                                ⚠️ Data jenazah tidak lengkap. Silahkan
                                <a href="{{ route('petugas.permohonan.edit', $permohonan) }}" class="fw-semibold">edit permohonan</a>
                                untuk mengisi NIK dan Nama Jenazah terlebih dahulu sebelum menyetujui.
                            @else
                                Data jenazah siap untuk disimpan. Tekan tombol "Setujui" untuk menyimpan data ke database jenazah.
                            @endif
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Data Ahli Waris -->
    <div class="detail-section">
        <div class="detail-section-title">
            <i class="bi bi-people"></i>
            Data Ahli Waris / Pemohon
        </div>
        <div class="detail-row">
            <div>
                <div class="detail-label">Nama</div>
                <div class="detail-value">
                    {{ $permohonan->nama_ahli_waris ?? '-' }}
                </div>
            </div>

            <div>
                <div class="detail-label">No. HP</div>
                <div class="detail-value">
                    {{ $permohonan->no_hp_ahli_waris ?? '-' }}
                </div>
            </div>

            <div>
                <div class="detail-label">Hubungan Keluarga</div>
                <div class="detail-value">
                    {{ $permohonan->hubungan_keluarga ?? '-' }}
                </div>
            </div>

            @if($permohonan->jenis_permohonan === 'perpanjangan')
                <div>
                    <div class="detail-label">Biaya Sewa Makam</div>

                    <div class="detail-value">
                        @if($permohonan->tpu_biaya_sewa)
                            <span class="detail-badge detail-badge-primary">
                                <i class="bi bi-cash-stack"></i>
                                {{ $permohonan->tpu_biaya_sewa }}
                            </span>
                        @else
                            -
                        @endif
                    </div>
                </div>
            @endif

            <div>
                <div class="detail-label">Alamat</div>
                <div class="detail-value">
                    {{ $permohonan->alamat ?? '-' }}
                </div>
            </div>

            <div>
                <div class="detail-label">Akun Pemohon</div>
                <div class="detail-value">
                    <span class="detail-badge detail-badge-primary">
                        {{ $permohonan->user?->name ?? 'Tidak terdaftar' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if($permohonan->jenis_permohonan === 'perpanjangan' && $permohonan->biayaRetribusi)
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="bi bi-cash-coin"></i>
                Biaya Retribusi
            </div>
            <div class="detail-row">
                <div>
                    <div class="detail-label">Nama Biaya</div>
                    <div class="detail-value">{{ $permohonan->biayaRetribusi->nama_biaya }}</div>
                </div>
                <div>
                    <div class="detail-label">Nominal</div>
                    <div class="detail-value">Rp {{ number_format($permohonan->biayaRetribusi->nominal, 0, ',', '.') }}</div>
                </div>
                <div>
                    <div class="detail-label">Rekening Tujuan</div>
                    <div class="detail-value">
                        {{ $permohonan->biayaRetribusi->nomor_rekening }}<br>
                        <small class="text-muted">{{ $permohonan->biayaRetribusi->nama_bank }} a.n. {{ $permohonan->biayaRetribusi->atas_nama_rekening }}</small>
                    </div>
                </div>
                <div>
                    <div class="detail-label">Status Pembayaran</div>
                    <div class="detail-value">
                        @php
                            $paymentBadgeClass = match($permohonan->status_pembayaran) {
                                'terverifikasi', 'tidak_ada_biaya' => 'detail-badge-success',
                                'ditolak' => 'detail-badge-danger',
                                default => 'detail-badge-warning',
                            };
                        @endphp
                        <span class="detail-badge {{ $paymentBadgeClass }}">
                            {{ $permohonan->statusPembayaranLabel() }}
                        </span>
                    </div>
                </div>
            </div>

            @if(! $permohonan->biayaRetribusi->isGratis())
                <div class="detail-row mt-3">
                    <div>
                        <div class="detail-label">Bukti Transfer</div>
                        <div class="detail-value">
                            @if($permohonan->bukti_transfer)
                                <a href="{{ asset('storage/' . $permohonan->bukti_transfer) }}" target="_blank" class="doc-link">
                                    <i class="bi bi-receipt"></i> Lihat Bukti Transfer
                                </a>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>

                <hr class="my-3">
                <form action="{{ route('petugas.permohonan.status-pembayaran', $permohonan) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Update Status Pembayaran</label>
                            <select name="status_pembayaran" class="form-select form-control-custom">
                                <option value="menunggu_verifikasi" @selected(old('status_pembayaran', $permohonan->status_pembayaran) === 'menunggu_verifikasi')>Menunggu Verifikasi</option>
                                <option value="terverifikasi" @selected(old('status_pembayaran', $permohonan->status_pembayaran) === 'terverifikasi')>Terverifikasi</option>
                                <option value="ditolak" @selected(old('status_pembayaran', $permohonan->status_pembayaran) === 'ditolak')>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="action-btn action-btn-approve w-100">
                                <i class="bi bi-check2-square me-2"></i> Simpan Status
                            </button>
                        </div>
                    </div>
                </form>
            @else
                <div class="alert alert-success border-2 border-dark shadow-sm mb-0 mt-3">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Permohonan perpanjangan ini tidak dikenakan biaya retribusi. Status pembayaran otomatis dianggap selesai.
                </div>
            @endif
        </div>
    @endif

    <!-- Dokumen -->
    <div class="detail-section">
        <div class="detail-section-title">
            <i class="bi bi-file-earmark"></i>
            Dokumen Pendukung
        </div>
        <div class="detail-row">
            @if($permohonan->scan_ktp_ahli_waris)
                <div>
                    <div class="detail-label">KTP Ahli Waris</div>
                    <a href="{{ asset('storage/' . $permohonan->scan_ktp_ahli_waris) }}" target="_blank" class="doc-link">
                        <i class="bi bi-file-pdf"></i> Lihat Dokumen
                    </a>
                </div>
            @endif

            @if($permohonan->scan_kk)
                <div>
                    <div class="detail-label">Kartu Keluarga</div>
                    <a href="{{ asset('storage/' . $permohonan->scan_kk) }}" target="_blank" class="doc-link">
                        <i class="bi bi-file-pdf"></i> Lihat Dokumen
                    </a>
                </div>
            @endif

            @if($permohonan->surat_kematian)
                <div>
                    <div class="detail-label">Surat Kematian</div>
                    <a href="{{ asset('storage/' . $permohonan->surat_kematian) }}" target="_blank" class="doc-link">
                        <i class="bi bi-file-pdf"></i> Lihat Dokumen
                    </a>
                </div>
            @endif

        </div>
    </div>

    <!-- Catatan -->
    @if($permohonan->catatan)
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="bi bi-chat-dots"></i>
                Catatan
            </div>
            <div class="detail-value">{{ $permohonan->catatan }}</div>
        </div>
    @endif

    @if($permohonan->catatan_revisi)
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="bi bi-pencil-square"></i>
                Catatan Revisi Dokumen
            </div>
            <div class="detail-value">{{ $permohonan->catatan_revisi }}</div>
        </div>
    @endif

    @if($permohonan->isDarurat())
        @php
            $missingAdministrativeFields = $permohonan->missingAdministrativeFields();
        @endphp
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="bi bi-clipboard2-check"></i>
                Status Kelengkapan Administrasi Darurat
            </div>
            @if(empty($missingAdministrativeFields))
                <div class="alert alert-success border-2 border-dark shadow-sm mb-0">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Seluruh data dan dokumen utama sudah lengkap. Permohonan darurat ini siap diselesaikan oleh petugas.
                </div>
            @else
                <div class="alert alert-warning border-2 border-dark shadow-sm mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Data belum lengkap. Bagian yang masih perlu dilengkapi:
                    <strong>{{ implode(', ', $missingAdministrativeFields) }}</strong>.
                </div>
            @endif
        </div>
    @endif

    <!-- Action Buttons (hanya jika status masih menunggu) -->
    @if($permohonan->jenis_permohonan === 'darurat' && $permohonan->status === 'menunggu_konfirmasi')
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="bi bi-lightning-charge"></i>
                Tindakan Darurat
            </div>
            <p class="text-muted mb-3">
                Jika TPU ini tidak dapat menampung pemakaman darurat ini (misal: makam kosong tidak tersedia atau di luar wilayah layanan), tolak permohonan dengan alasan yang jelas agar keluarga dapat segera mencari TPU lain.
            </p>
            <div class="row g-3">
                <div class="col-md-6">
                    <form action="{{ route('petugas.permohonan.proses-darurat', $permohonan) }}" method="POST">
                        @csrf
                        <button type="submit" class="action-btn action-btn-approve w-100">
                            <i class="bi bi-lightning-charge-fill me-2"></i> Proses Darurat
                        </button>
                    </form>
                </div>
                <div class="col-md-6">
                    <button class="action-btn action-btn-reject w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-circle me-2"></i> Tolak
                    </button>
                </div>
            </div>
        </div>
    @elseif($permohonan->jenis_permohonan === 'darurat' && $permohonan->status === 'diproses_darurat')
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="bi bi-geo-alt"></i>
                Selesaikan Pemakaman Darurat
            </div>
            <form action="{{ route('petugas.permohonan.selesaikan-pemakaman', $permohonan) }}" method="POST">
                @csrf

                {{-- ===== TAMBAHAN: Jenis Pemakaman (Baru / Tumpang Sari) untuk darurat =====
                     Sebelumnya form ini hanya bisa memilih makam kosong. Sekarang petugas
                     bisa memilih tumpang sari jika makam yang cocok sudah terisi
                     (misalnya makam keluarga). --}}
                <div class="mb-3 tipe-pemakaman-toggle-group">
                    <label class="form-label">Jenis Pemakaman</label>
                    <div class="d-flex gap-2 tipe-pemakaman-toggle">
                        <div class="form-check">
                            <input class="form-check-input js-tipe-radio" type="radio" name="tipe_pemakaman"
                                   id="tipeBaruDarurat" value="baru" checked>
                            <label class="form-check-label" for="tipeBaruDarurat">Makam Baru</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input js-tipe-radio" type="radio" name="tipe_pemakaman"
                                   id="tipeTumpangSariDarurat" value="tumpang_sari">
                            <label class="form-check-label" for="tipeTumpangSariDarurat">Tumpang Sari</label>
                        </div>
                    </div>

                    <div class="mb-3 mt-3 js-makam-baru-wrapper">
                        <label class="form-label">Pilih Makam Kosong</label>
                        <select name="makam_id" class="form-select form-control-custom js-makam-baru-select">
                            <option value="">Pilih makam kosong</option>
                            @foreach($makamKosong as $makam)
                                <option value="{{ $makam->id }}" @selected($permohonan->makam_id == $makam->id)>
                                    {{ $makam->kode_makam }} - {{ $makam->blok ?? '-' }} / No {{ $makam->nomor ?? '-' }} / {{ $makam->keterangan ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 mt-3 d-none js-makam-tumpang-sari-wrapper">
                        <label class="form-label">Pilih Makam Tujuan (Tumpang Sari)</label>
                        <select name="makam_id" class="form-select form-control-custom js-makam-tumpang-sari-select" disabled>
                            <option value="">-- Pilih makam yang sudah terisi --</option>
                            @foreach($makamTerisi as $makam)
                                <option value="{{ $makam->id }}">
                                    {{ $makam->kode_makam }} - {{ $makam->blok ?? '-' }} / No {{ $makam->nomor ?? '-' }}
                                    @if($makam->jenazahs->isNotEmpty())
                                        (sudah berisi: {{ $makam->jenazahs->pluck('nama')->join(', ') }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">
                            Gunakan opsi ini jika ahli waris meminta pemakaman darurat digabungkan
                            dengan makam keluarga yang sudah terisi.
                        </small>
                    </div>
                </div>
                {{-- ===== AKHIR TAMBAHAN ===== --}}

                <div class="mb-3">
                    <label class="form-label">Catatan Petugas</label>
                    <textarea name="catatan" class="form-control form-control-custom" rows="3">{{ old('catatan', $permohonan->catatan) }}</textarea>
                </div>
                <button type="submit" class="action-btn action-btn-approve w-100">
                    <i class="bi bi-check2-square me-2"></i> Selesaikan Pemakaman
                </button>
            </form>
        </div>
    @elseif($permohonan->status === 'menunggu_verifikasi_dokumen')
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="bi bi-file-earmark-check"></i>
                Verifikasi Dokumen Darurat
            </div>
            <form action="{{ route('petugas.permohonan.verifikasi-dokumen', $permohonan) }}" method="POST">
                @csrf

                {{-- Tenggat sewa makam diisi saat dokumen disetujui, alurnya sama
                     seperti permohonan makam baru reguler (lihat Modal Approve di bawah) --}}
                <div class="mb-3">
                    <label class="form-label">Tenggat Sewa Makam</label>
                    <input
                        type="date"
                        name="tenggat_sewa_makam"
                        class="form-control form-control-custom @error('tenggat_sewa_makam') is-invalid @enderror"
                        value="{{ old('tenggat_sewa_makam', optional($permohonan->tenggat_sewa_makam ?? $renewalDueAt)->format('Y-m-d')) }}"
                    >
                    <small class="text-muted d-block mt-1">Isi tanggal batas akhir masa sewa makam (berlaku saat dokumen disetujui).</small>
                    @error('tenggat_sewa_makam')
                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Catatan Revisi</label>
                    <textarea name="catatan_revisi" class="form-control form-control-custom" rows="3" placeholder="Isi jika dokumen perlu diperbaiki.">{{ old('catatan_revisi') }}</textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <button type="submit" name="aksi" value="setujui" class="action-btn action-btn-approve w-100">
                            <i class="bi bi-check-circle me-2"></i> Setujui Dokumen
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" name="aksi" value="perbaikan" class="action-btn action-btn-reject w-100">
                            <i class="bi bi-arrow-counterclockwise me-2"></i> Perlu Perbaikan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @elseif($permohonan->status === 'menunggu' || $permohonan->status === 'pending')
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="bi bi-check2-all"></i>
                Ambil Tindakan
            </div>

            <div class="row g-3 mb-3">
                <div class="col-lg-4">
                    <a href="{{ route('petugas.permohonan.edit', $permohonan) }}" class="action-btn w-100" style="background: #1E3E62; color: #fff; border-color: #1E3E62; text-decoration: none;">
                        <i class="bi bi-pencil-square me-2"></i> Edit Data
                    </a>
                </div>
                <div class="col-lg-4">
                    <button class="action-btn action-btn-approve w-100" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="bi bi-check-circle me-2"></i> Setujui
                    </button>
                </div>
                <div class="col-lg-4">
                    <button class="action-btn action-btn-reject w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-circle me-2"></i> Tolak
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="bi bi-info-circle"></i>
                Aksi
            </div>
            <a href="{{ route('petugas.permohonan.edit', $permohonan) }}" class="action-btn" style="background: #1E3E62; color: #fff; border-color: #1E3E62; text-decoration: none; display: inline-flex;">
                <i class="bi bi-pencil-square me-2"></i> Edit Data
            </a>
        </div>
    @endif
</div>

<!-- Modal Approve -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content modal-custom">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle me-2"></i> Setujui Permohonan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('petugas.permohonan.approve', $permohonan) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-3">Anda akan menyetujui permohonan ini. Apakah ada catatan untuk ahli waris?</p>

                    {{-- Jenis Pemakaman (Baru / Tumpang Sari). Hanya relevan untuk
                         permohonan makam baru. Perpanjangan/darurat punya alurnya
                         sendiri dan tidak perlu opsi ini di sini. --}}
                    @if($permohonan->jenis_permohonan === 'makam_baru')
                        <div class="mb-3 tipe-pemakaman-toggle-group">
                            <label class="form-label">Jenis Pemakaman</label>
                            <div class="d-flex gap-2 tipe-pemakaman-toggle">
                                <div class="form-check">
                                    <input class="form-check-input js-tipe-radio" type="radio" name="tipe_pemakaman"
                                           id="tipeBaru" value="baru" checked>
                                    <label class="form-check-label" for="tipeBaru">Makam Baru</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input js-tipe-radio" type="radio" name="tipe_pemakaman"
                                           id="tipeTumpangSari" value="tumpang_sari">
                                    <label class="form-check-label" for="tipeTumpangSari">Tumpang Sari</label>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">
                                Cek catatan ahli waris di atas &mdash; jika berisi permintaan tumpang sari,
                                pilih "Tumpang Sari" lalu tentukan makam tujuannya di bawah.
                            </small>

                            <div class="mb-3 mt-3 js-makam-baru-wrapper">
                                <label class="form-label">Pilih Makam Kosong</label>
                                <select name="makam_id" class="form-select form-control-custom js-makam-baru-select">
                                    <option value="">-- Tidak mengubah makam saat ini --</option>
                                    @foreach($makamKosong as $makam)
                                        <option value="{{ $makam->id }}" @selected($permohonan->makam_id == $makam->id)>
                                            {{ $makam->kode_makam }} - {{ $makam->blok ?? '-' }} / No {{ $makam->nomor ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3 mt-3 d-none js-makam-tumpang-sari-wrapper">
                                <label class="form-label">Pilih Makam Tujuan (Tumpang Sari)</label>
                                <select name="makam_id" class="form-select form-control-custom js-makam-tumpang-sari-select" disabled>
                                    <option value="">-- Pilih makam yang sudah terisi --</option>
                                    @foreach($makamTerisi as $makam)
                                        <option value="{{ $makam->id }}">
                                            {{ $makam->kode_makam }} - {{ $makam->blok ?? '-' }} / No {{ $makam->nomor ?? '-' }}
                                            @if($makam->jenazahs->isNotEmpty())
                                                (sudah berisi: {{ $makam->jenazahs->pluck('nama')->join(', ') }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">
                                    Daftar ini hanya menampilkan makam yang sudah terisi, beserta nama jenazah
                                    yang sudah dimakamkan, untuk memudahkan pencocokan dengan permintaan ahli waris.
                                </small>
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Tenggat Sewa Baru</label>
                        <input type="date" name="tenggat_sewa_makam" class="form-control form-control-custom" value="{{ old('tenggat_sewa_makam', optional($permohonan->tenggat_sewa_makam ?? $renewalDueAt)->format('Y-m-d')) }}">
                        <small class="text-muted d-block mt-1">Isi tanggal tenggat baru jika ini permohonan perpanjangan makam lama.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea name="catatan" class="form-control form-control-custom" rows="3" placeholder="Masukkan catatan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i> Setujui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content modal-custom">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle me-2"></i> Tolak Permohonan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('petugas.permohonan.reject', $permohonan) }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if($permohonan->jenis_permohonan === 'darurat')
                        <p class="text-muted mb-3">
                            Anda akan menolak permohonan pemakaman darurat ini. Karena keluarga sedang dalam kondisi mendesak, jelaskan alasan penolakan secara spesifik agar mereka tahu langkah selanjutnya.
                        </p>
                        <div class="mb-3">
                            <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="catatan" class="form-control form-control-custom" rows="4" placeholder="Contoh: Makam kosong di TPU ini sudah penuh / lokasi wafat di luar wilayah layanan TPU ini. Silakan segera hubungi TPU terdekat lainnya." required></textarea>
                            @error('catatan')
                                <small class="text-danger d-block mt-2">{{ $message }}</small>
                            @enderror
                        </div>
                    @else
                        <p class="text-muted mb-3">Anda akan menolak permohonan ini. Harap berikan alasan penolakan.</p>
                        <div class="mb-3">
                            <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="catatan" class="form-control form-control-custom" rows="4" placeholder="Masukkan alasan penolakan..." required></textarea>
                            @error('catatan')
                                <small class="text-danger d-block mt-2">{{ $message }}</small>
                            @enderror
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i> Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== Script toggle tipe pemakaman (digeneralisasi) =====
     Sebelumnya script ini hanya mengenali satu grup toggle lewat ID tetap
     (tipeBaru/tipeTumpangSari/dst), sehingga tidak bisa dipakai ulang di form
     "Selesaikan Pemakaman Darurat". Sekarang script bekerja dengan men-scan
     SETIAP elemen ".tipe-pemakaman-toggle-group" di halaman dan mengatur
     toggle-nya masing-masing secara independen, sehingga aman dipakai di
     modal approve maupun form darurat sekaligus. --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var groups = document.querySelectorAll('.tipe-pemakaman-toggle-group');

        groups.forEach(function (group) {
            var radios = group.querySelectorAll('.js-tipe-radio');
            var makamBaruWrapper = group.querySelector('.js-makam-baru-wrapper');
            var makamTumpangSariWrapper = group.querySelector('.js-makam-tumpang-sari-wrapper');
            var makamBaruSelect = group.querySelector('.js-makam-baru-select');
            var makamTumpangSariSelect = group.querySelector('.js-makam-tumpang-sari-select');

            if (!radios.length || !makamBaruWrapper || !makamTumpangSariWrapper) {
                return;
            }

            function toggleTipePemakaman() {
                var checked = group.querySelector('.js-tipe-radio:checked');
                var tipe = checked ? checked.value : 'baru';

                if (tipe === 'tumpang_sari') {
                    makamBaruWrapper.classList.add('d-none');
                    makamTumpangSariWrapper.classList.remove('d-none');
                    if (makamBaruSelect) makamBaruSelect.disabled = true;
                    if (makamTumpangSariSelect) makamTumpangSariSelect.disabled = false;
                } else {
                    makamBaruWrapper.classList.remove('d-none');
                    makamTumpangSariWrapper.classList.add('d-none');
                    if (makamBaruSelect) makamBaruSelect.disabled = false;
                    if (makamTumpangSariSelect) makamTumpangSariSelect.disabled = true;
                }
            }

            radios.forEach(function (radio) {
                radio.addEventListener('change', toggleTipePemakaman);
            });

            toggleTipePemakaman();
        });
    });
</script>
@endpush
@endsection
