@extends('layouts.app')

@section('content')
<style>
    body {
        background: linear-gradient(135deg, #d6dee6, #c3d0db);
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
    }

    .login-container {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .login-card {
        background: #ffffff;
        border-radius: 14px;
        overflow: hidden;
        max-width: 850px;
        width: 100%;
        display: flex;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    }

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
        margin: 0;
    }

    .left-panel span {
        font-weight: bold;
        font-size: 24px;
        letter-spacing: 1px;
    }

    .right-panel {
        flex: 1;
        padding: 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .form-wrapper {
        max-width: 320px;
        width: 100%;
        margin: 0 auto;
    }

    .login-title {
        font-weight: 700;
        margin-bottom: 25px;
        color: #1E3E62;
        font-size: 20px;
        text-align: center;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-label {
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 6px;
        color: #2c3e50;
        display: block;
    }

    .form-control {
        width: 100%;
        border-radius: 6px;
        background: #ffffff;
        border: 1px solid #bfc9d4;
        padding: 12px 14px;
        font-size: 14px;
    }

    .form-control:focus {
        border-color: #1E3E62;
        box-shadow: 0 0 0 1px rgba(30, 62, 98, 0.2);
        outline: none;
    }

    .form-check {
        margin-top: 5px;
        margin-bottom: 20px;
    }

    .form-check-label {
        font-size: 13px;
    }

    .btn-login {
        background-color: #1E3E62;
        color: white;
        border-radius: 6px;
        padding: 10px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        width: 100%;
    }

    .btn-login:hover {
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
        padding: 8px 12px;
        font-size: 13px;
        margin-bottom: 15px;
    }

    .field-error {
        display: block;
        margin-top: 6px;
        color: #c0392b;
        font-size: 12px;
    }

    @media (max-width: 768px) {
        .login-card {
            flex-direction: column;
        }
    }
</style>

<div class="login-container">
    <div class="login-card">
        <div class="left-panel">
            <h4>Selamat Datang di <span>TAMPU</span></h4>
        </div>

        <div class="right-panel">
            <div class="form-wrapper">
                <div class="login-title">Login Sistem</div>

                @if(session('error'))
                    <div class="alert alert-danger text-center">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success text-center">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.proses') }}">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        @error('email')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" minlength="8" required>
                        @error('password')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" @checked(old('remember'))>
                        <label class="form-check-label" for="remember">
                            Ingat Saya
                        </label>
                    </div>

                    <button class="btn-login">
                        Login
                    </button>

                    <div class="small-link">
                        <small>
                            Belum punya akun?
                            <a href="{{ route('register') }}">Daftar disini</a>
                        </small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection