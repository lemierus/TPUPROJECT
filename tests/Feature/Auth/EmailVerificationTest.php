@extends('layouts.app')

@section('title', 'Verifikasi Email')

@section('content')
<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-7">

            <div class="card shadow border-0">

                <div class="card-body p-5 text-center">

                    <div class="mb-4">
                        <i class="fas fa-envelope-open-text text-primary"
                           style="font-size:70px;"></i>
                    </div>

                    <h3 class="fw-bold mb-3">
                        Verifikasi Email
                    </h3>

                    <p class="text-muted">
                        Terima kasih telah mendaftar.
                    </p>

                    <p class="text-muted">
                        Kami telah mengirimkan link verifikasi ke alamat email Anda.
                        Silakan buka email tersebut kemudian klik tombol
                        <strong>Verifikasi Email</strong>.
                    </p>

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success">
                            Link verifikasi baru telah dikirim ke email Anda.
                        </div>
                    @endif

                    <form method="POST"
                          action="{{ route('verification.send') }}">
                        @csrf

                        <button class="btn btn-primary px-4">
                            Kirim Ulang Email Verifikasi
                        </button>
                    </form>

                    <hr class="my-4">

                    <form method="POST"
                          action="{{ route('logout') }}">
                        @csrf

                        <button class="btn btn-outline-danger">
                            Logout
                        </button>
                    </form>

                </div>

            </div>

        </div>

    </div>

</div>
@endsection