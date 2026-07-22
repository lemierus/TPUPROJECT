<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Admin')</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    body {
        background-color: #f4f6f9;
    }

    .sidebar {
        width: 250px;
        min-height: 100vh;
        background: #1E3E62;
        color: white;
        position: fixed;
        top: 0;
        left: 0;
        padding: 20px;
    }

    .sidebar a {
        color: white;
        text-decoration: none;
        display: block;
        padding: 10px 12px;
        border-radius: 8px;
        margin-bottom: 5px;
    }

    .sidebar a:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .content {
        margin-left: 250px;
        padding: 25px 25px 20px 25px;
    }

    .navbar-admin {
        background: white;
        border-radius: 15px;
        padding: 15px 20px;
        box-shadow: 0px 2px 8px rgba(0,0,0,0.05);

        margin-bottom: 10px; 
    }

    .text-custom {
        color: #1E3E62 !important;
    }

    .navbar-user-link {
        display: inline-flex;
        align-items: center;
        gap: .75rem;
        color: #111827;
        text-decoration: none;
    }

    .navbar-user-link:hover {
        color: #1E3E62;
    }

    .navbar-user-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        border: 2px solid #1E3E62;
        overflow: hidden;
        background: #ecf2ff;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
    }

    .navbar-user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .navbar-user-avatar span {
        font-weight: 800;
        color: #1E3E62;
        font-size: .95rem;
    }

    .navbar-user-meta {
        line-height: 1.1;
    }
</style>

    @stack('styles')
</head>
<body>

    {{-- Sidebar --}}
    @php
        $currentUser = auth()->user();
        $dashboardRoute = match ($currentUser?->role) {
            'admin' => 'admin.dashboard',
            'petugas' => 'petugas.dashboard',
            'kepala' => 'kepala.dashboard',
            'kdlh' => 'kdlh.dashboard',
            default => 'user.dashboard',
        };
        $masterPrefix = match (true) {
            $currentUser?->isPetugas() => 'petugas',
            $currentUser?->isKepala() => 'kepala',
            $currentUser?->isKdlh() => 'kdlh',
            default => 'admin',
        };
    @endphp

    <div class="sidebar">
        <h5 class="fw-bold mb-4" onclick="window.location='{{ url('/') }}'" style="cursor: pointer;">
            <i class="bi bi-building"></i> TAMPU
        </h5>

        <a href="{{ route($dashboardRoute) }}">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>

        @if($currentUser?->isAdmin() || $currentUser?->isPetugas() || $currentUser?->isKepala() || $currentUser?->isKdlh())
            <a href="{{ route($masterPrefix.'.data-jenazah') }}">
                <i class="bi bi-people-fill me-2"></i> Data Jenazah
            </a>

            <a href="{{ route($masterPrefix.'.data-makam') }}">
                <i class="bi bi-geo-alt-fill me-2"></i> Data Makam
            </a>

            @if($currentUser?->isAdmin() || $currentUser?->isPetugas())
                <a href="{{ $currentUser?->isPetugas() ? route('petugas.permohonan') : route('admin.master.permohonan') }}">
                    <i class="bi bi-envelope-paper-fill me-2"></i> Permohonan
                </a>
            @endif

            @if($currentUser?->isAdmin() || $currentUser?->isPetugas() || $currentUser?->isKepala() || $currentUser?->isKdlh())
                <a href="{{ $currentUser?->isKepala() ? route('kepala.laporan') : ($currentUser?->isKdlh() ? route('kdlh.laporan') : route($masterPrefix.'.master.laporan')) }}">
                    <i class="bi bi-file-earmark-text-fill me-2"></i> Laporan TPU
                </a>
            @endif
        @endif

        @if($currentUser?->isKdlh())
            <a href="{{ route('kdlh.tpu.index') }}">
                <i class="bi bi-map me-2"></i> Kelola TPU
            </a>

            <a href="{{ route('kdlh.biaya-retribusi.index') }}">
                <i class="bi bi-cash-coin me-2"></i> Biaya Retribusi
            </a>
        @endif

        @if($currentUser?->isAdmin() || $currentUser?->isKepala() || $currentUser?->isKdlh())
            <a href="{{ route($currentUser?->isKepala() ? 'kepala.users.index' : ($currentUser?->isKdlh() ? 'kdlh.users.index' : 'admin.users.index')) }}">
                <i class="bi bi-person-gear me-2"></i> Kelola User
            </a>
        @endif


        <hr class="border-light">

        @unless($currentUser?->isAdmin())
            <a href="{{ route('profile.show') }}">
                <i class="bi bi-person-circle me-2"></i> Profil
            </a>
        @endunless

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn text-start w-100 text-white border-0" style="padding:10px 12px;">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </button>
        </form>
    </div>

    {{-- Content --}}
    <div class="content">

        {{-- Navbar --}}
        <div class="navbar-admin d-flex justify-content-between align-items-center">
            <div>
                <span class="fw-bold text-dark">Sistem Informasi TPU</span>
            </div>

            <div class="text-end">
                <div class="d-flex align-items-center justify-content-end gap-3">
                    @if($currentUser?->isAdmin())
                        <div class="navbar-user-link">
                            <span class="navbar-user-avatar">
                                @if($currentUser?->profile_photo_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($currentUser->profile_photo_path) }}" alt="Foto profil {{ $currentUser->name }}">
                                @else
                                    <span>{{ $currentUser?->initials() ?? 'U' }}</span>
                                @endif
                            </span>

                            <span class="navbar-user-meta text-start">
                                <span class="fw-bold text-custom d-block">{{ $currentUser->name ?? 'Pengguna' }}</span>
                                <small class="text-muted d-block text-capitalize">{{ $currentUser->role ?? '-' }}</small>
                            </span>
                        </div>
                    @else
                        <a href="{{ route('profile.show') }}" class="navbar-user-link">
                            <span class="navbar-user-avatar">
                                @if($currentUser?->profile_photo_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($currentUser->profile_photo_path) }}" alt="Foto profil {{ $currentUser->name }}">
                                @else
                                    <span>{{ $currentUser?->initials() ?? 'U' }}</span>
                                @endif
                            </span>

                            <span class="navbar-user-meta text-start">
                                <span class="fw-bold text-custom d-block">{{ $currentUser->name ?? 'Pengguna' }}</span>
                                <small class="text-muted d-block text-capitalize">{{ $currentUser->role ?? '-' }}</small>
                            </span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        @yield('content')
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
