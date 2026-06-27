@extends('admin.layouts.app')

@section('title', 'Profile')

@push('styles')
<style>
    .profile-shell {
        position: relative;
    }

    .profile-card {
        border: 2px solid #111827;
        border-radius: 24px;
        background: #fff;
        box-shadow: 0 12px 0 rgba(17, 24, 39, 0.08);
        overflow: hidden;
    }

    .profile-header {
        background: #f8fafc;
        border-bottom: 2px solid #111827;
        padding: 1.15rem 1.25rem;
    }

    .profile-section-title {
        font-weight: 800;
        color: #101828;
        letter-spacing: -.02em;
        margin-bottom: 0;
    }

    .profile-body {
        padding: 1.5rem;
    }

    .profile-avatar-wrapper {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 3px solid #1E3E62;
        overflow: hidden;
        display: grid;
        place-items: center;
        background: #f4f6f9;
        flex: 0 0 auto;
    }

    .profile-avatar-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-avatar-placeholder {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1E3E62;
        text-transform: uppercase;
    }

    .profile-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .42rem .72rem;
        border-radius: 999px;
        border: 1.5px solid #111827;
        font-weight: 700;
        font-size: .82rem;
        white-space: nowrap;
        background: #ecf2ff;
        color: #1E3E62;
    }

    .profile-submit-btn {
        border: 2px solid #111827;
        background: #1E3E62;
        color: #fff;
        font-weight: 800;
        border-radius: 14px;
        padding: .7rem 2rem;
        box-shadow: 0 8px 0 rgba(17, 24, 39, 0.08);
        transition: all .18s ease;
    }

    .profile-submit-btn:hover {
        background: #152d47;
        transform: translateY(-2px);
        box-shadow: 0 12px 0 rgba(17, 24, 39, 0.10);
        color: #fff;
    }
</style>
@endpush

@section('content')
@php
    $currentUser = auth()->user();
@endphp
<div class="container-fluid py-4 profile-shell">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="profile-card">
                <div class="profile-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="profile-section-title">Profile Saya</h4>
                        <p class="text-muted mb-0">Lihat dan perbarui informasi akun Anda.</p>
                    </div>
                    <span class="profile-pill">
                        <i class="bi bi-shield-check"></i>
                        {{ strtoupper($currentUser->role) }}
                    </span>
                </div>

                <div class="profile-body">
                    @if(session('success'))
                        <div class="alert alert-success border-2 border-dark shadow-sm mb-4">
                            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    <div class="d-flex align-items-center gap-4 mb-4 pb-3 border-bottom border-2 flex-wrap">
                        <div class="profile-avatar-wrapper">
                            @if($currentUser->profile_photo_path)
                                <img src="{{ Storage::url($currentUser->profile_photo_path) }}" alt="Foto Profil">
                            @else
                                <div class="profile-avatar-placeholder">
                                    {{ $currentUser->initials() ?: substr($currentUser->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">{{ $currentUser->name }}</h5>
                            <p class="text-muted mb-1">{{ $currentUser->email }}</p>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge rounded-pill text-bg-light border border-dark-subtle text-capitalize">
                                    {{ $currentUser->role }}
                                </span>
                                @if($currentUser->tpu)
                                    <span class="badge rounded-pill text-bg-light border border-dark-subtle">
                                        TPU: {{ $currentUser->tpu }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror border-2"
                                    value="{{ old('name', $currentUser->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror border-2"
                                    value="{{ old('email', $currentUser->email) }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            @unless($currentUser->isUser())
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">NIP</label>
                                    <input type="text" name="nip"
                                        class="form-control @error('nip') is-invalid @enderror border-2"
                                        value="{{ old('nip', $currentUser->nip) }}">
                                    @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endunless

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">No. HP</label>
                                <input type="text" name="no_hp"
                                    class="form-control @error('no_hp') is-invalid @enderror border-2"
                                    value="{{ old('no_hp', $currentUser->no_hp) }}">
                                @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Foto Profil</label>
                                <input type="file" name="profile_photo"
                                    class="form-control @error('profile_photo') is-invalid @enderror border-2"
                                    accept="image/*">
                                <small class="text-muted">Maksimal 2MB. Format: JPG, PNG.</small>
                                @error('profile_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <hr class="border-2">
                                <h6 class="fw-bold mb-2">Ganti Password <small class="text-muted">(kosongkan jika tidak ingin ganti)</small></h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password Baru</label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror border-2">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation"
                                    class="form-control border-2">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-2">
                            <button type="submit" class="profile-submit-btn">
                                <i class="bi bi-check2-circle me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
