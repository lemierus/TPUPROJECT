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
            padding: 20px;
        }

        .navbar-admin {
            background: white;
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0px 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .text-custom {
            color: #1E3E62 !important;
        }       
    </style>

    @stack('styles')
</head>
<body>

    {{-- Sidebar --}}
    <div class="sidebar">
        <h5 class="fw-bold mb-4">
            <i class="bi bi-building"></i> TAMPU
        </h5>

        <a href="{{ route('dashboard') }}">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>

        <a href="/data-jenazah">
            <i class="bi bi-people-fill me-2"></i> Data Jenazah
        </a>

        <a href="/data-makam">
            <i class="bi bi-geo-alt-fill me-2"></i> Data Makam
        </a>

        <a href="#">
            <i class="bi bi-envelope-paper-fill me-2"></i> Permohonan
        </a>

        <a href="#">
            <i class="bi bi-file-earmark-text-fill me-2"></i> Laporan
        </a>

        <hr class="border-light">

        <a href="#">
            <i class="bi bi-gear-fill me-2"></i> Pengaturan
        </a>
    </div>

    {{-- Content --}}
    <div class="content">

        {{-- Navbar --}}
        <div class="navbar-admin d-flex justify-content-between align-items-center">
            <div>
                <span class="fw-bold text-dark">Sistem Informasi Pemakaman</span>
            </div>

            <div class="text-end">
                <span class="fw-bold text-custom">Petugas</span>
                <small class="d-block text-muted">{{ now()->format('d-m-Y') }}</small>
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
