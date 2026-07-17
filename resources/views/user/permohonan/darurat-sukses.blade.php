@extends('admin.layouts.app')

@section('title', 'Permohonan Darurat Berhasil')

@section('content')
<div class="container py-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-lg-5">
            <div class="text-center mb-4">
                <div class="display-6 text-danger mb-3">
                    <i class="bi bi-exclamation-diamond-fill"></i>
                </div>
                <h3 class="fw-bold mb-2">Permohonan Darurat Berhasil Dikirim</h3>
                <p class="text-muted mb-0">
                    Permohonan darurat untuk jenazah <strong>{{ $permohonan->nama_jenazah }}</strong> telah masuk ke sistem TAMPU
                    dengan status <strong>{{ $permohonan->statusLabel() }}</strong>.
                </p>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">TPU Tujuan</div>
                        <div class="fw-semibold">{{ $permohonan->tpu }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">Nama Ahli Waris</div>
                        <div class="fw-semibold">{{ $permohonan->nama_ahli_waris }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">Nomor HP</div>
                        <div class="fw-semibold">{{ $permohonan->no_hp_ahli_waris }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small">Catatan</div>
                        <div class="fw-semibold">{{ $permohonan->catatan ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning border-2 border-dark shadow-sm mb-4">
                <i class="bi bi-info-circle-fill me-2"></i>
                Karena ini permohonan darurat, pemakaman bisa diproses lebih dulu oleh petugas.
                Setelah itu, Anda mungkin akan diminta melengkapi dokumen administrasi dari dashboard ahli waris.
            </div>

            <div class="d-flex justify-content-center gap-2 flex-wrap">
                @if($waPetugasUrl)
                    <a href="{{ $waPetugasUrl }}" target="_blank" class="btn btn-success">
                        <i class="bi bi-whatsapp me-1"></i> Hubungi Petugas TPU via WhatsApp
                    </a>
                @endif
                <a href="{{ route('user.dashboard') }}" class="btn btn-outline-dark">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
