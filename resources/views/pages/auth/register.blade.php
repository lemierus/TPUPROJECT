@extends('layouts.app')

@section('content')
<style>
    body {
        background: linear-gradient(135deg, #d6dee6, #c3d0db);
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
    }

    .register-container {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .register-card {
        background: #ffffff;
        border-radius: 14px;
        overflow: hidden;
        max-width: 900px;
        width: 100%;
        display: flex;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    }

    /* LEFT */
    .left-panel {
        flex: 1;
        background: #1E3E62;
        color: white;
        padding: 35px 25px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .left-panel h4 {
        font-size: 20px;
        font-weight: 500;
    }

    .left-panel span {
        font-weight: bold;
        font-size: 24px;
    }

    .left-panel img {
        width: 90px;
        margin-top: 20px;
    }

    /* RIGHT */
    .right-panel {
        flex: 1;
        padding: 40px;
    }

    .form-wrapper {
        max-width: 350px;
        margin: auto;
    }

    .register-title {
        font-weight: 700;
        margin-bottom: 25px;
        color: #1E3E62;
        font-size: 20px;
        text-align: center;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-label {
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 5px;
        display: block;
    }

    .form-control {
        width: 100%;
        border-radius: 6px;
        border: 1px solid #bfc9d4;
        padding: 11px 12px;
        font-size: 14px;
    }

    .form-control:focus {
        border-color: #1E3E62;
        box-shadow: 0 0 0 1px rgba(30,62,98,0.2);
        outline: none;
    }

    .btn-register {
        background-color: #1E3E62;
        color: white;
        border: none;
        border-radius: 6px;
        padding: 10px;
        width: 100%;
        margin-top: 10px;
    }

    .btn-register:hover {
        background-color: #16324F;
    }

    .small-link {
        margin-top: 15px;
        text-align: center;
    }

    .small-link a {
        color: #1E3E62;
        text-decoration: none;
        font-weight: 500;
    }

    .small-link a:hover {
        text-decoration: underline;
    }

    .alert {
        font-size: 13px;
        padding: 8px;
        margin-bottom: 10px;
    }

    @media (max-width: 768px) {
        .register-card {
            flex-direction: column;
        }
    }
</style>

<div class="register-container">

    <div class="register-card">

        {{-- LEFT --}}
        <div class="left-panel">
            <h4>Registrasi <span>TAMPU</span></h4>
            <p style="font-size:15px;">Sistem Informasi TPU</p>
        </div>

        {{-- RIGHT --}}
        <div class="right-panel">

            <div class="form-wrapper">

                <div class="register-title">Buat Akun</div>

                {{-- ERROR --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register.proses') }}">
                    @csrf

                    {{-- NAMA --}}
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    {{-- EMAIL --}}
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    {{-- PASSWORD --}}
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    {{-- KONFIRMASI --}}
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    {{-- BUTTON --}}
                    <button class="btn-register">
                        Daftar
                    </button>

                    {{-- LINK --}}
                    <div class="small-link">
                        <small>
                            Sudah punya akun?
                            <a href="{{ route('login') }}">Login disini</a>
                        </small>
                    </div>

                </form>

            </div>

        </div>

    </div>

</div>
@endsection
