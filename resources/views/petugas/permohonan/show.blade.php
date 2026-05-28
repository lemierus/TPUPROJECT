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
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <a href="{{ route('petugas.permohonan') }}" class="btn btn-sm btn-outline-dark mb-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-2 border-dark shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-2 border-dark shadow-sm mb-4">
            <i class="bi bi-exclamation-circle-fill me-2"></i>{{ $errors->first() }}
        </div>
    @endif

    <div class="detail-header d-flex justify-content-between align-items-start gap-3 flex-wrap">
        <div>
            <h3 class="mb-2">Detail Permohonan</h3>
            <p class="mb-0 opacity-75">ID: #{{ $permohonan->id }} |
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
                    default => 'detail-badge-warning'
                };
                $statusLabel = match($status) {
                    'disetujui' => 'Disetujui',
                    'ditolak' => 'Ditolak',
                    default => 'Menunggu'
                };
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
                <strong>Data Jenazah Tersimpan:</strong> Data jenazah sudah disimpan ke halaman data jenazah dengan ID: #{{ $permohonan->jenazah_id }}
            @else
                <strong>Data Jenazah Belum Tersimpan:</strong> Data jenazah belum disimpan. Pastikan NIK dan nama jenazah sudah diisi sebelum menyetujui permohonan ini.
            @endif
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
                <div class="detail-badge {{ $permohonan->jenis_permohonan === 'perpanjangan' ? 'detail-badge-primary' : 'detail-badge-success' }}">
                    {{ $permohonan->jenis_permohonan === 'perpanjangan' ? 'Perpanjangan Makam' : 'Makam Baru' }}
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
        </div>
    </div>

    <!-- Data Jenazah / Makam -->
    @if($permohonan->jenis_permohonan === 'perpanjangan')
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="bi bi-tree"></i>
                Data Makam
            </div>
            <div class="detail-row">
                <div>
                    <div class="detail-label">Kode Makam</div>
                    <div class="detail-value">{{ $permohonan->makam?->kode_makam ?? '-' }}</div>
                </div>
                <div>
                    <div class="detail-label">Blok / Zona</div>
                    <div class="detail-value">{{ $permohonan->blok_zona_makam ?? '-' }}</div>
                </div>
                <div>
                    <div class="detail-label">Nomor Makam</div>
                    <div class="detail-value">{{ $permohonan->no_makam ?? '-' }}</div>
                </div>
                <div>
                    <div class="detail-label">Tahun Pemakaman</div>
                    <div class="detail-value">{{ $permohonan->tahun_pemakaman ?? '-' }}</div>
                </div>
            </div>
        </div>
    @else
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="bi bi-person"></i>
                Data Jenazah
            </div>
            <div class="detail-row">
                <div>
                    <div class="detail-label">Nama</div>
                    <div class="detail-value">{{ $permohonan->nama_jenazah ?? '-' }}</div>
                </div>
                <div>
                    <div class="detail-label">NIK</div>
                    <div class="detail-value">{{ $permohonan->nik_jenazah ?? '-' }}</div>
                </div>
                <div>
                    <div class="detail-label">Jenis Kelamin</div>
                    <div class="detail-value">{{ $permohonan->jenis_kelamin ?? '-' }}</div>
                </div>
                <div>
                    <div class="detail-label">Agama</div>
                    <div class="detail-value">{{ $permohonan->agama ?? '-' }}</div>
                </div>
                <div>
                    <div class="detail-label">Tempat Lahir</div>
                    <div class="detail-value">{{ $permohonan->tempat_lahir ?? '-' }}</div>
                </div>
                <div>
                    <div class="detail-label">Tanggal Lahir</div>
                    <div class="detail-value">
                        @if($permohonan->tanggal_lahir)
                            {{ \Carbon\Carbon::parse($permohonan->tanggal_lahir)->format('d F Y') }}
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div>
                    <div class="detail-label">Tanggal Wafat</div>
                    <div class="detail-value">
                        @if($permohonan->tanggal_wafat)
                            {{ \Carbon\Carbon::parse($permohonan->tanggal_wafat)->format('d F Y') }}
                        @else
                            -
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Status Integrasi Data Jenazah -->
    @if($permohonan->jenis_permohonan === 'makam_baru')
        <div class="detail-section">
            <div class="detail-section-title">
                <i class="bi bi-link-45deg"></i>
                Status Integrasi Data Jenazah
            </div>
            <div class="detail-row">
                <div>
                    <div class="detail-label">Status Penyimpanan ke Database Jenazah</div>
                    @if($permohonan->jenazah_id)
                        <div class="detail-badge detail-badge-success">
                            <i class="bi bi-check-circle"></i>
                            Tersimpan (ID: #{{ $permohonan->jenazah_id }})
                        </div>
                        <p class="text-muted mt-2 mb-0">
                            Data jenazah sudah berhasil disimpan ke database data jenazah TPU {{ $permohonan->tpu }}
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
                <div class="detail-value">{{ $permohonan->nama_ahli_waris ?? '-' }}</div>
            </div>
            <div>
                <div class="detail-label">No. HP</div>
                <div class="detail-value">{{ $permohonan->no_hp_ahli_waris ?? '-' }}</div>
            </div>
            <div>
                <div class="detail-label">Hubungan Keluarga</div>
                <div class="detail-value">{{ $permohonan->hubungan_keluarga ?? '-' }}</div>
            </div>
            <div>
                <div class="detail-label">Alamat</div>
                <div class="detail-value">{{ $permohonan->alamat ?? '-' }}</div>
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

            @if($permohonan->bukti_pembayaran_retribusi)
                <div>
                    <div class="detail-label">Bukti Pembayaran Retribusi</div>
                    <a href="{{ asset('storage/' . $permohonan->bukti_pembayaran_retribusi) }}" target="_blank" class="doc-link">
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

    <!-- Action Buttons (hanya jika status masih menunggu) -->
    @if($permohonan->status === 'menunggu' || $permohonan->status === 'pending')
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
                    <p class="text-muted mb-3">Anda akan menolak permohonan ini. Harap berikan alasan penolakan.</p>
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="catatan" class="form-control form-control-custom" rows="4" placeholder="Masukkan alasan penolakan..." required></textarea>
                        @error('catatan')
                            <small class="text-danger d-block mt-2">{{ $message }}</small>
                        @enderror
                    </div>
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
@endsection
