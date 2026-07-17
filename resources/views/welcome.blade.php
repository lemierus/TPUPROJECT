<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Informasi TPU</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

    <style>
        :root {
            --brand: #1E3E62;
            --brand-dark: #10263f;
            --ink: #0f172a;
            --muted: #667085;
            --surface: #ffffff;
            --soft: #f5f8fc;
            --line: #d0d7e2;
            --accent: #f8fafc;
            --shadow: 0 16px 40px rgba(16, 38, 63, 0.10);
            --shadow-strong: 0 20px 0 rgba(16, 38, 63, 0.10);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Instrument Sans', system-ui, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(30, 62, 98, 0.08), transparent 28%),
                radial-gradient(circle at top right, rgba(30, 62, 98, 0.06), transparent 24%),
                #f4f7fb;
            color: var(--ink);
        }

        a {
            text-decoration: none;
        }

        .landing-wrap {
            position: relative;
            overflow: hidden;
        }

        .landing-wrap::before,
        .landing-wrap::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        .landing-wrap::before {
            width: 280px;
            height: 280px;
            background: rgba(30, 62, 98, 0.08);
            top: -120px;
            right: -80px;
        }

        .landing-wrap::after {
            width: 200px;
            height: 200px;
            background: rgba(16, 38, 63, 0.05);
            bottom: 140px;
            left: -90px;
        }

        .landing-shell {
            position: relative;
            z-index: 1;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1.1rem 0;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: .8rem;
            color: var(--ink);
            font-weight: 800;
            letter-spacing: .02em;
        }

        .brand-mark {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: var(--brand);
            color: #fff;
            display: grid;
            place-items: center;
            border: 2px solid var(--ink);
            box-shadow: 0 10px 0 rgba(16, 38, 63, 0.15);
            flex: 0 0 auto;
        }

        .brand small {
            color: var(--muted);
            font-weight: 600;
            display: block;
            margin-top: .15rem;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .btn-brutal {
            border: 2px solid var(--ink);
            border-radius: 14px;
            font-weight: 800;
            box-shadow: 0 8px 0 rgba(16, 38, 63, 0.08);
            transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease;
        }

        .btn-brutal:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 0 rgba(16, 38, 63, 0.10);
        }

        .hero {
            margin-top: .5rem;
            padding: 1.25rem 0 2rem;
        }

        .hero-card {
            position: relative;
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(10px);
            border: 2px solid var(--ink);
            border-radius: 28px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .hero-card::after {
            content: '';
            position: absolute;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: rgba(30, 62, 98, 0.08);
            right: -70px;
            bottom: -110px;
        }

        .hero-copy {
            position: relative;
            z-index: 1;
            padding: 2rem;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .45rem .85rem;
            border-radius: 999px;
            border: 2px solid var(--brand);
            background: #eef5ff;
            color: var(--brand);
            font-size: .83rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .hero-title {
            font-size: clamp(2rem, 4vw, 4rem);
            line-height: .98;
            letter-spacing: -.05em;
            font-weight: 800;
            margin: 1rem 0 1rem;
            max-width: 11ch;
        }

        .hero-text {
            color: var(--muted);
            font-size: 1.05rem;
            line-height: 1.7;
            max-width: 56rem;
        }

        .hero-meta {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
            margin-top: 1.35rem;
        }

        .meta-chip {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .6rem .85rem;
            border-radius: 999px;
            border: 2px solid var(--line);
            background: #fff;
            font-weight: 700;
            color: #344054;
        }

        .hero-panel {
            position: relative;
            z-index: 1;
            background: linear-gradient(180deg, #1E3E62, #10263f);
            color: #fff;
            border-left: 2px solid var(--ink);
            min-height: 100%;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .hero-panel .panel-title {
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: .85rem;
        }

        .hero-panel .panel-copy {
            color: rgba(255,255,255,.82);
            line-height: 1.7;
        }

        .panel-stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .9rem;
            margin-top: 1.25rem;
        }

        .panel-stat {
            background: rgba(255,255,255,.08);
            border: 1.5px solid rgba(255,255,255,.22);
            border-radius: 18px;
            padding: .95rem;
        }

        .panel-stat strong {
            display: block;
            font-size: 1.5rem;
            line-height: 1;
            margin-bottom: .25rem;
        }

        .panel-stat span {
            color: rgba(255,255,255,.8);
            font-size: .92rem;
        }

        .section-title {
            font-weight: 800;
            letter-spacing: -.03em;
            color: var(--ink);
            margin-bottom: .4rem;
        }

        .section-text {
            color: var(--muted);
            max-width: 56rem;
        }

        .info-card {
            height: 100%;
            background: #fff;
            border: 2px solid var(--ink);
            border-radius: 22px;
            box-shadow: 0 14px 0 rgba(16, 38, 63, 0.08);
            overflow: hidden;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .info-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 0 rgba(16, 38, 63, 0.10);
            border-color: var(--brand);
        }

        .info-card-header {
            padding: 1rem 1rem .85rem;
            background: #f8fbff;
            border-bottom: 2px solid var(--ink);
        }

        .info-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .3rem .65rem;
            border-radius: 999px;
            border: 1.5px solid var(--line);
            background: #fff;
            color: var(--brand);
            font-size: .82rem;
            font-weight: 800;
        }

        .info-card-body {
            padding: 1rem;
        }

        .info-list {
            display: grid;
            gap: .7rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .info-list li {
            display: flex;
            gap: .65rem;
            align-items: flex-start;
            color: #344054;
        }

        .info-list i {
            color: var(--brand);
            margin-top: .15rem;
        }

        .tpu-card {
            height: 100%;
            background: #fff;
            border: 2px solid var(--ink);
            border-radius: 18px;
            box-shadow: 0 4px 16px rgba(16, 38, 63, 0.10);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .tpu-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(16, 38, 63, 0.14);
            border-color: var(--brand);
        }

        .tpu-head {
            background: var(--brand);
            color: #fff;
            padding: 1.25rem;
            border-bottom: 2px solid var(--ink);
        }

        .tpu-tag {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .28rem .65rem;
            border-radius: 999px;
            border: 1.5px solid rgba(255,255,255,.35);
            background: rgba(255,255,255,.12);
            font-size: .78rem;
            font-weight: 700;
            margin-bottom: .65rem;
        }

        .tpu-body {
            padding: 1.25rem;
            color: #344054;
            flex: 1;
        }

        .tpu-footer {
            padding: 0 1.25rem 1.25rem;
        }

        .tpu-footer hr {
            border-color: #e5e7eb;
            margin-bottom: .85rem;
        }

        .tpu-feature-list {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: .85rem;
        }

        .tpu-feature-list span {
            padding: .50rem .75rem;
            border-radius: 999px;
            border: 1.5px solid #d1d5db;
            background: #f8fafc;
            font-size: .78rem;
            font-weight: 600;
            color: #344054;
        }

        .floating-note {
            border: 2px solid var(--ink);
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 14px 0 rgba(16, 38, 63, 0.08);
            padding: 1.15rem;
        }

        .cta-band {
            background: var(--brand);
            color: #fff;
            border: 2px solid var(--ink);
            border-radius: 24px;
            box-shadow: 0 16px 0 rgba(16, 38, 63, 0.12);
            overflow: hidden;
        }

        .cta-band .cta-copy {
            padding: 1.4rem;
        }

        .cta-band p {
            color: rgba(255,255,255,.82);
        }

        .footer-note {
            color: var(--muted);
            font-size: .95rem;
            padding: 1.25rem 0 2rem;
        }

        @media (max-width: 991.98px) {
            .hero-panel {
                border-left: 0;
                border-top: 2px solid var(--ink);
            }
        }

        @media (max-width: 767.98px) {
            .hero-copy,
            .hero-panel,
            .info-card-body,
            .tpu-body,
            .cta-band .cta-copy {
                padding: 1rem;
            }

            .hero-title {
                max-width: none;
            }

            .panel-stats {
                grid-template-columns: 1fr;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
@php
    $tpus = $landingTpus ?? [];
    $isAuthenticated = auth()->check();
    $dashboardHref = $isAuthenticated ? route('dashboard') : route('login');
    $ajukanHref = $isAuthenticated ? route('user.dashboard') : route('login');
@endphp

<div class="landing-wrap">
    <div class="container landing-shell">
        <div class="topbar">
            <a href="{{ url('/') }}" class="brand">
                <span class="brand-mark"><i class="bi bi-building-check"></i></span>
                <span>
                    TAMPU
                    <small>Website Tempat Pemakaman Umum Terintegrasi</small>
                </span>
            </a>

            <div class="nav-actions">
                <a href="{{ route('login') }}" class="btn btn-outline-dark btn-brutal px-4 py-2">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                </a>
                <a href="{{ route('register') }}" class="btn btn-dark btn-brutal px-4 py-2" style="background: var(--brand);">
                    <i class="bi bi-person-plus me-1"></i> Register
                </a>
            </div>
        </div>

        <section class="hero">
            <div class="hero-card">
                <div class="row g-0">
                    <div class="col-lg-7">
                        <div class="hero-copy">
                            <div class="eyebrow">
                                <i class="bi bi-stars"></i>
                                Landing Page Resmi TAMPU
                            </div>
                            <h1 class="hero-title">
                                Satu pintu digital untuk layanan TPU yang lebih cepat, transparan, dan terintegrasi.
                            </h1>
                            <p class="hero-text">
                                Sistem Informasi TPU membantu ahli waris dan pengelola layanan pemakaman mengakses informasi,
                                memilih lokasi TPU, serta mengajukan permohonan dengan alur yang lebih rapi. Informasi TPU
                                terintegrasi disajikan agar proses pencarian dan pengajuan menjadi lebih mudah.
                            </p>

                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <a href="{{ $ajukanHref }}" class="btn btn-dark btn-brutal px-4 py-3" style="background: var(--brand);">
                                    <i class="bi bi-send me-1"></i>
                                    Ajukan Permohonan
                                </a>
                            </div>

                            <div class="hero-meta">
                                <div class="meta-chip"><i class="bi bi-shield-check"></i> Terpusat & aman</div>
                                <div class="meta-chip"><i class="bi bi-diagram-3"></i> Alur terintegrasi</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="hero-panel">
                            <div>
                                <div class="panel-title">Ringkasan Sistem</div>
                                <p class="panel-copy mb-0">
                                    TAMPU dirancang untuk mempertemukan data TPU, permohonan ahli waris, dan proses verifikasi
                                    dalam satu pengalaman yang mudah dipahami.
                                </p>

                                <div class="panel-stats">
                                    <div class="panel-stat">
                                        <strong>{{ count($tpus) }}</strong>
                                        <span>TPU terintegrasi</span>
                                    </div>
                                    <div class="panel-stat">
                                        <strong>1</strong>
                                        <span>alur layanan digital</span>
                                    </div>
                                    <div class="panel-stat">
                                        <strong>2</strong>
                                        <span>aksi utama: login & daftar</span>
                                    </div>
                                    <div class="panel-stat">
                                        <strong>24/7</strong>
                                        <span>akses informasi kapan saja</span>
                                    </div>
                                </div>
                            </div>

                            <div class="floating-note mt-4">
                                <div class="fw-bold text-dark mb-1">Untuk Ahli Waris</div>
                                <div class="text-muted">
                                    Jika sudah punya akun, silahkan login untuk memilih TPU terlebih dahulu,
                                    lalu lanjut ke pengajuan permohonan.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="pb-4">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
                <div>
                    <h2 class="section-title h3">Apa yang ditawarkan TAMPU?</h2>
                    <p class="section-text mb-0">
                        Informasi inti ditata untuk mendukung pengunjung umum, ahli waris, dan pengelola layanan.
                    </p>
                </div>
            </div>

            <div class="row g-3 g-lg-4">
                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-card-header">
                            <span class="info-badge"><i class="bi bi-graph-up-arrow"></i> Efisien</span>
                        </div>
                        <div class="info-card-body">
                            <h5 class="fw-bold mb-2">Pengajuan lebih singkat</h5>
                            <p class="text-muted mb-0">
                                Alur layanan dibuat ringkas agar ahli waris bisa memahami langkah pengajuan tanpa proses yang membingungkan.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-card-header">
                            <span class="info-badge"><i class="bi bi-shield-check"></i> Transparan</span>
                        </div>
                        <div class="info-card-body">
                            <h5 class="fw-bold mb-2">Status permohonan jelas</h5>
                            <p class="text-muted mb-0">
                                Status permohonan dapat dipantau dari dashboard agar pengguna mengetahui tahap proses secara real time.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-card-header">
                            <span class="info-badge"><i class="bi bi-map"></i> Terhubung</span>
                        </div>
                        <div class="info-card-body">
                            <h5 class="fw-bold mb-2">TPU dalam satu sistem</h5>
                            <p class="text-muted mb-0">
                                Daftar TPU ditampilkan sebagai satu ekosistem layanan yang dapat diperbarui.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="pb-4">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
                <div>
                    <h2 class="section-title h3">TPU terintegrasi</h2>
                    <p class="section-text mb-0">
                        Daftar TPU ditampilkan sebagai pilihan informasi dan referensi pengajuan layanan.
                    </p>
                </div>
            </div>

            <div class="row g-3 g-lg-4">
                @foreach($tpus as $tpu)
                    <div class="col-lg-4 col-md-6">
                        <div class="tpu-card">
                            <div class="tpu-head">
                                <span class="tpu-tag"><i class="bi bi-geo-alt"></i> Terintegrasi</span>
                                <h5 class="fw-bold mb-1">{{ $tpu['nama'] }}</h5>
                                <div class="opacity-75 small">{{ $tpu['lokasi'] }}</div>
                            </div>

                            <div class="tpu-body">
                                <p class="mb-2 small">{{ $tpu['ringkasan'] }}</p>
                                <p class="fw-semibold text-dark mb-3 small">{{ $tpu['highlight'] }}</p>

                                <div class="tpu-feature-list">
                                    <span>
                                        <i class="bi bi-grid-3x3-gap me-1"></i>
                                        {{ $tpu['makam_tersedia'] }} makam tersedia
                                    </span>
                                    <span><i class="bi bi-map me-1"></i>Info lokasi</span>
                                    <span><i class="bi bi-phone me-1"></i>Layanan digital</span>
                                    <span><i class="bi bi-check2-circle me-1"></i>Tertata</span>
                                </div>
                            </div>

                            @if($tpu['wa_nomor'])
                                <div class="tpu-footer">
                                    <!-- <hr> -->
                                    <p class="small text-muted mb-2 fw-semibold">
                                        <i class="bi bi-whatsapp text-success me-1"></i>
                                        Butuh penguburan segera?
                                    </p>
                                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', $tpu['wa_nomor']) }}" target="_blank"
                                        class="btn btn-success btn-sm w-100 fw-semibold"
                                        style="border-radius: 8px;">
                                        <i class="bi bi-whatsapp me-1"></i>
                                        Hubungi {{ $tpu['wa_nama'] ?? 'Petugas' }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="pb-4">
            <div class="cta-band">
                <div class="row g-0 align-items-center">
                    <div class="col-lg-8">
                        <div class="cta-copy">
                            <h3 class="fw-bold mb-2">Sudah punya akun ahli waris?</h3>
                            <p class="mb-0">
                                Masuk ke dashboard untuk memilih salah satu TPU terlebih dahulu, lalu lanjutkan ke form pengajuan permohonan.
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="cta-copy text-lg-end">
                            <a href="{{ route('register') }}" class="btn btn-outline-light btn-brutal px-4 py-3 mb-2 mb-lg-0">
                                <i class="bi bi-person-plus me-1"></i>
                                Buat Akun
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="footer-note d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>© {{ date('Y') }} TAMPU — Website Tempat Pemakaman Umum Terintegrasi</span>
        </div>
    </div>
</div>
</body>
</html>
