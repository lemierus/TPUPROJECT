@extends('admin.layouts.app')

@section('title', 'Ringkasan Data Pemakaman')

@push('styles')
<style>
    .summary-shell {
        position: relative;
    }

    .summary-hero {
        border: 2px solid #111827;
        border-radius: 24px;
        background: linear-gradient(135deg, #f8fbff 0%, #ffffff 60%, #eef4ff 100%);
        box-shadow: 0 16px 0 rgba(17, 24, 39, 0.08);
        overflow: hidden;
    }

    .summary-hero h1 {
        font-weight: 800;
        letter-spacing: -.03em;
        color: #101828;
    }

    .summary-hero p {
        color: #475467;
    }

    .summary-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .45rem .75rem;
        border-radius: 999px;
        border: 1.5px solid #111827;
        background: #fff;
        font-weight: 700;
        font-size: .82rem;
    }

    .summary-card {
        height: 100%;
        border: 2px solid #111827;
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 12px 0 rgba(17, 24, 39, 0.08);
        overflow: hidden;
    }

    .summary-card-header {
        background: #1E3E62;
        color: #fff;
        padding: 1rem 1.1rem;
        border-bottom: 2px solid #111827;
    }

    .summary-card-header h5 {
        margin-bottom: 0;
        font-weight: 800;
    }

    .summary-card-body {
        padding: 1.1rem;
    }

    .summary-table {
        margin-bottom: 0;
    }

    .summary-table th,
    .summary-table td {
        padding: .72rem .8rem;
        vertical-align: top;
        border-color: #e4e7ec;
    }

    .summary-table th {
        width: 42%;
        color: #475467;
        font-weight: 700;
        background: #f9fafb;
    }

    .summary-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .42rem .72rem;
        border-radius: 999px;
        border: 1.5px solid #111827;
        font-weight: 700;
        font-size: .82rem;
        white-space: nowrap;
    }

    .summary-pill-success {
        background: #e8fff2;
        color: #027a48;
    }

    .summary-pill-primary {
        background: #ecf2ff;
        color: #1E3E62;
    }

    .summary-pill-warning {
        background: #fef3c7;
        color: #b45309;
    }

    .summary-pill-danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .summary-note {
        border: 2px dashed #d0d5dd;
        background: #fcfcfd;
        border-radius: 18px;
        padding: 1rem;
        color: #475467;
    }

    @media (max-width: 767.98px) {
        .summary-hero,
        .summary-card {
            border-radius: 18px;
        }
    }
</style>
@endpush

@section('content')
@php
    $jenazahData = $jenazah ?? $permohonan->jenazah;
    $jenazahMakamData = $jenazahData?->makam;
    $makamData = $jenazahMakamData ?? $makam ?? $permohonan->makam;
    $renewalDueAt = $permohonan->renewalDueAt();
    $renewalLevel = $permohonan->renewalAlertLevel();
    $renewalJenazahId = $jenazahData?->id ?? $permohonan->jenazah_id;
@endphp

<div class="container-fluid py-4 summary-shell">
    <div class="summary-hero p-4 p-lg-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
            <div>
                <div class="summary-badge mb-3">
                    <i class="bi bi-file-earmark-text"></i>
                    Ringkasan Data Pemakaman
                </div>
                <h1 class="mb-3">Data jenazah, ahli waris, dan makam dalam satu halaman</h1>
                <p class="mb-0">
                    Permohonan ini sudah disetujui, jadi Anda bisa melihat rangkuman informasi yang terhubung
                    antara data pengajuan, data jenazah, dan data makam tujuan.
                </p>
            </div>

            <div class="text-lg-end">
                <div class="summary-pill summary-pill-success mb-2">
                    <i class="bi bi-check2-circle"></i>
                    {{ ucfirst(str_replace('_', ' ', $permohonan->status)) }}
                </div>
                <div class="summary-pill summary-pill-primary d-block mb-2">
                    <i class="bi bi-building"></i>
                    {{ $permohonan->tpu }}
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-2 border-dark shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($renewalDueAt)
        @php
            $renewalTitle = $renewalLevel === 'expired'
                ? 'Masa sewa makam sudah melewati batas 2 tahun'
                : ($renewalLevel === 'soon' ? 'Masa sewa makam mendekati batas 2 tahun' : 'Masa sewa makam masih aman');
            $renewalMessage = $renewalLevel === 'expired'
                ? 'Silakan segera ajukan perpanjangan sewa makam ke petugas TPU terkait.'
                : ($renewalLevel === 'soon'
                    ? 'Mohon siapkan perpanjangan sewa makam sebelum batas waktu berakhir.'
                    : 'Tenggat sewa masih jauh, silakan pantau secara berkala.');
            $renewalClass = $renewalLevel === 'expired'
                ? 'alert-danger'
                : ($renewalLevel === 'soon' ? 'alert-warning' : 'alert-success');
        @endphp
        <div class="alert {{ $renewalClass }} border-2 border-dark shadow-sm mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2">
                <div>
                    <div class="fw-bold mb-1">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $renewalTitle }}
                    </div>
                    <div class="mb-0">
                        Dalam waktu 2 tahun sejak permohonan ini disetujui, ahli waris wajib melakukan perpanjangan sewa makam.
                        Batas perpanjangan untuk data ini adalah <strong>{{ $renewalDueAt->format('d-m-Y') }}</strong>.
                        {{ $renewalMessage }}
                    </div>
                </div>
                <div class="text-md-end">
                    @if($renewalLevel === 'expired')
                        <span class="summary-pill summary-pill-danger">
                            <i class="bi bi-calendar-event"></i>
                            Lewat batas
                        </span>
                    @elseif($renewalLevel === 'soon')
                        <span class="summary-pill summary-pill-warning">
                            <i class="bi bi-calendar-event"></i>
                            Batas mendekat
                        </span>
                    @else
                        <span class="summary-pill summary-pill-success">
                            <i class="bi bi-calendar-event"></i>
                            Aman
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if(in_array($renewalLevel, ['soon', 'expired'], true) && $renewalJenazahId)
        <div class="alert alert-primary border-2 border-dark shadow-sm mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                <div>
                    <div class="fw-bold mb-1">
                        <i class="bi bi-arrow-repeat me-2"></i>Ajukan Perpanjangan Sewa Makam
                    </div>
                    <div class="mb-0">
                        Klik tombol berikut untuk membuka form perpanjangan sewa makam {{ $permohonan->tpu }}.
                        Data jenazah dan data makam akan terbawa otomatis sesuai data yang sudah diperbarui petugas.
                    </div>
                </div>
                <a href="{{ route('user.permohonan.create', ['tpu' => $permohonan->tpu, 'jenis_permohonan' => 'perpanjangan', 'jenazah_id' => $renewalJenazahId, 'source_permohonan_id' => $permohonan->id]) }}" class="btn btn-dark">
                    <i class="bi bi-calendar-plus me-1"></i> Perpanjang Sewa Makam
                </a>
            </div>
        </div>
    @endif

    <div class="row g-3 g-lg-4">
        <div class="col-lg-4">
            <div class="summary-card">
                <div class="summary-card-header">
                    <h5><i class="bi bi-person-vcard me-2"></i>Data Jenazah</h5>
                </div>
                <div class="summary-card-body">
                    <table class="table table-sm summary-table">
                        <tbody>
                            <tr>
                                <th>Nama</th>
                                <td>{{ $jenazahData->nama ?? $permohonan->nama_jenazah ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>NIK</th>
                                <td>{{ $jenazahData->nik ?? $permohonan->nik_jenazah ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jenis Kelamin</th>
                                <td>{{ $jenazahData->jenis_kelamin ?? $permohonan->jenis_kelamin ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tempat Lahir</th>
                                <td>{{ $jenazahData->tempat_lahir ?? $permohonan->tempat_lahir ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Lahir</th>
                                <td>
                                    {{ $jenazahData?->tanggal_lahir?->format('d-m-Y') ?? ($permohonan->tanggal_lahir ? \Illuminate\Support\Carbon::parse($permohonan->tanggal_lahir)->format('d-m-Y') : '-') }}
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Wafat</th>
                                <td>
                                    {{ $jenazahData?->tanggal_wafat?->format('d-m-Y') ?? ($permohonan->tanggal_wafat ? \Illuminate\Support\Carbon::parse($permohonan->tanggal_wafat)->format('d-m-Y') : '-') }}
                                </td>
                            </tr>
                            <tr>
                                <th>Agama</th>
                                <td>{{ $jenazahData->agama ?? $permohonan->agama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td>{{ $jenazahData->alamat ?? $permohonan->alamat ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Keterangan</th>
                                <td>{{ $jenazahData->keterangan ?? $permohonan->keterangan ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="summary-card">
                <div class="summary-card-header">
                    <h5><i class="bi bi-people-fill me-2"></i>Data Ahli Waris</h5>
                </div>
                <div class="summary-card-body">
                    <table class="table table-sm summary-table">
                        <tbody>
                            <tr>
                                <th>Nama Ahli Waris</th>
                                <td>{{ $permohonan->nama_ahli_waris ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>No HP</th>
                                <td>{{ $permohonan->no_hp_ahli_waris ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Hubungan Keluarga</th>
                                <td>{{ $permohonan->hubungan_keluarga ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Nama Pemohon</th>
                                <td>{{ $permohonan->nama_pemohon ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Petugas Penanggungjawab</th>
                                <td>{{ $permohonan->petugas?->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Catatan</th>
                                <td>{{ $permohonan->catatan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Waktu Disetujui</th>
                                <td>{{ $permohonan->updated_at?->format('d-m-Y H:i') ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="summary-card">
                <div class="summary-card-header">
                    <h5><i class="bi bi-geo-alt-fill me-2"></i>Data Makam</h5>
                </div>
                <div class="summary-card-body">
                    <table class="table table-sm summary-table">
                        <tbody>
                            <tr>
                                <th>Kode Makam</th>
                                <td>{{ $jenazahData->kode_makam ?? $makamData?->kode_makam ?? $permohonan->kode_makam ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Blok</th>
                                <td>{{ $jenazahData->blok ?? $makamData?->blok ?? $permohonan->blok ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Zona</th>
                                <td>{{ $jenazahData->zona ?? $makamData?->zona ?? $permohonan->zona ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Nomor</th>
                                <td>{{ $jenazahData->nomor_makam ?? $makamData?->nomor ?? $permohonan->nomor_makam ?? $permohonan->no_makam ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>TPU</th>
                                <td>{{ $jenazahData->tpu ?? $makamData?->tpu ?? $permohonan->tpu ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Keterangan Makam</th>
                                <td>{{ $jenazahData->keterangan ?? $makamData?->keterangan ?? $permohonan->keterangan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jenis Permohonan</th>
                                <td>
                                    <span class="summary-pill summary-pill-primary">
                                        <i class="bi bi-plus-circle"></i>
                                        {{ ucfirst(str_replace('_', ' ', $permohonan->jenis_permohonan)) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="summary-note mt-4">
        <div class="d-flex flex-column flex-md-row align-items-start gap-2">
            <i class="bi bi-info-circle-fill fs-4 text-primary"></i>
            <div>
                <div class="fw-bold text-dark mb-1">Catatan</div>
                <div>
                    Halaman ini hanya tersedia untuk permohonan <strong>pembuatan makam baru</strong> yang sudah disetujui.
                    Jika ada data yang belum tampil lengkap, pastikan petugas sudah menyetujui permohonan pada TPU tujuan.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
