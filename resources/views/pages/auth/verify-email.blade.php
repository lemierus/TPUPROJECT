@extends('layouts.app')

@section('content')
<style>
    body {
        background: linear-gradient(135deg, #d6dee6, #c3d0db);
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
    }

    .verify-container {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .verify-card {
        background: #ffffff;
        border-radius: 14px;
        overflow: hidden;
        max-width: 920px;
        width: 100%;
        display: flex;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    }

    .left-panel {
        flex: 1;
        background: #1E3E62;
        color: white;
        padding: 40px 28px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .left-panel h4 {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .left-panel p {
        font-size: 14px;
        line-height: 1.7;
        margin: 0;
        max-width: 280px;
        color: rgba(255, 255, 255, 0.9);
    }

    .right-panel {
        flex: 1;
        padding: 42px 38px;
        display: flex;
        align-items: center;
    }

    .verify-wrapper {
        width: 100%;
        max-width: 380px;
        margin: 0 auto;
    }

    .verify-title {
        color: #1E3E62;
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .verify-subtitle {
        color: #52606d;
        font-size: 14px;
        line-height: 1.7;
        margin-bottom: 20px;
    }

    .email-badge {
        display: inline-block;
        background: #eef4fb;
        color: #1E3E62;
        border: 1px solid #c8d7e6;
        border-radius: 999px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 18px;
        word-break: break-word;
    }

    .alert {
        font-size: 13px;
        border-radius: 10px;
    }

    .verify-actions {
        display: grid;
        gap: 12px;
        margin-top: 22px;
    }

    .btn-primary-custom,
    .btn-outline-custom {
        width: 100%;
        border-radius: 8px;
        padding: 11px 14px;
        font-size: 14px;
        font-weight: 600;
    }

    .btn-primary-custom {
        background: #1E3E62;
        color: white;
        border: none;
    }

    .btn-primary-custom:hover {
        background: #16324F;
        color: white;
    }

    .btn-outline-custom {
        background: white;
        color: #1E3E62;
        border: 1px solid #1E3E62;
    }

    .btn-outline-custom:hover {
        background: #f4f8fc;
        color: #1E3E62;
    }

    .verify-help {
        margin-top: 18px;
        font-size: 13px;
        color: #6b7785;
        line-height: 1.7;
    }

    @media (max-width: 768px) {
        .verify-card {
            flex-direction: column;
        }
    }
</style>

<div class="verify-container">
    <div class="verify-card">
        <div class="left-panel">
            <h4>Verifikasi Akun TAMPU</h4>
            <p>
                Satu langkah lagi. Silakan buka email Anda lalu klik tautan verifikasi
                agar akun dapat digunakan untuk mengakses layanan permohonan TPU.
            </p>
        </div>

        <div class="right-panel">
            <div class="verify-wrapper">
                <div class="verify-title">Cek Email Anda</div>
                <div class="verify-subtitle">
                    Kami telah mengirim tautan verifikasi ke alamat email berikut.
                    Setelah email diverifikasi, Anda akan diarahkan ke dashboard sesuai role akun.
                </div>

                @if(auth()->check())
                    <div class="email-badge">{{ auth()->user()->email }}</div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('status') === 'verification-link-sent')
                    <div class="alert alert-success">
                        Link verifikasi baru berhasil dikirim. Silakan cek inbox atau folder spam email Anda.
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="verify-actions">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary-custom">
                            Kirim Ulang Email Verifikasi
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-custom">
                            Logout
                        </button>
                    </form>
                </div>

                <div class="verify-help">
                    Jika email belum terlihat, periksa folder spam/junk. Pastikan alamat email
                    yang didaftarkan sudah benar.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
