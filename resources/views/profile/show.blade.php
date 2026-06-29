@extends('admin.layouts.app')

@section('title', 'Profile Saya')

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
        width: 128px;
        height: 128px;
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
        font-size: 2.75rem;
        font-weight: 800;
        color: #1E3E62;
        text-transform: uppercase;
    }

    .profile-meta-card {
        border: 1.5px solid #d0d5dd;
        border-radius: 18px;
        background: #f8fafc;
        padding: 1rem 1.1rem;
        height: 100%;
    }

    .profile-meta-label {
        font-size: .88rem;
        color: #667085;
        margin-bottom: .15rem;
    }

    .profile-meta-value {
        font-weight: 700;
        color: #101828;
        word-break: break-word;
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

    .profile-edit-btn {
        border: 2px solid #111827;
        background: #1E3E62;
        color: #fff;
        font-weight: 800;
        border-radius: 14px;
        padding: .75rem 1.2rem;
        box-shadow: 0 8px 0 rgba(17, 24, 39, 0.08);
        transition: all .18s ease;
    }

    .profile-edit-btn:hover {
        background: #152d47;
        transform: translateY(-2px);
        box-shadow: 0 12px 0 rgba(17, 24, 39, 0.10);
        color: #fff;
    }

    @media (max-width: 767.98px) {
        .profile-edit-btn {
            position: static;
            width: 100%;
            margin-top: 1rem;
        }
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
                        <p class="text-muted mb-0">Informasi akun yang tersimpan di sistem.</p>
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

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="profile-meta-card">
                                <div class="profile-meta-label">Nama Lengkap</div>
                                <div class="profile-meta-value">{{ $currentUser->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-meta-card">
                                <div class="profile-meta-label">Email</div>
                                <div class="profile-meta-value">{{ $currentUser->email }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-meta-card">
                                <div class="profile-meta-label">No. HP</div>
                                <div class="profile-meta-value">{{ $currentUser->no_hp ?? '-' }}</div>
                            </div>
                        </div>
                        @unless($currentUser->isUser())
                            <div class="col-md-6">
                                <div class="profile-meta-card">
                                    <div class="profile-meta-label">NIP</div>
                                    <div class="profile-meta-value">{{ $currentUser->nip ?? '-' }}</div>
                                </div>
                            </div>
                        @endunless
                        <div class="col-md-6">
                            <div class="profile-meta-card">
                                <div class="profile-meta-label">Role</div>
                                <div class="profile-meta-value text-capitalize">{{ $currentUser->role }}</div>
                            </div>
                        </div>
                        @if($currentUser->tpu)
                            <div class="col-md-6">
                                <div class="profile-meta-card">
                                    <div class="profile-meta-label">TPU</div>
                                    <div class="profile-meta-value">{{ $currentUser->tpu }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="d-flex justify-content-end mt-4 pt-3 border-top border-2">
                        <a href="{{ route('profile.edit.page') }}" class="profile-edit-btn text-decoration-none">
                            <i class="bi bi-pencil-square me-1"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
