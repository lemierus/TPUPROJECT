@extends('admin.layouts.app')

@section('title', 'Laporan Pemakaman')

@push('styles')
<style>
    .quick-filter-chip {
        border: 2px solid #d0d5dd;
        background: #fff;
        color: #475467;
        border-radius: 999px;
        padding: .4rem 1rem;
        font-size: .85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .15s ease;
    }

    .quick-filter-chip:hover {
        border-color: #1E3E62;
        color: #1E3E62;
    }

    .quick-filter-chip.active {
        background: #1E3E62;
        border-color: #1E3E62;
        color: #fff;
    }

    .stat-group-title {
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #667085;
        margin-bottom: .75rem;
    }

    .stat-card {
        background: #fff;
        border: 1px solid #eaecf0;
        border-left: 4px solid #98a2b3;
        border-radius: 0 12px 12px 0;
        padding: 1rem 1.1rem;
        height: 100%;
    }

    .stat-card i {
        font-size: 1.3rem;
    }

    .stat-card-label {
        color: #667085;
        font-size: .85rem;
        margin: .5rem 0 .1rem;
    }

    .stat-card-value {
        font-weight: 800;
        margin: 0;
        color: #101828;
    }

    .laporan-table th {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .02em;
        color: #667085;
    }

    .laporan-catatan {
        display: block;
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .export-btn {
        background: #1E3E62;
        color: #fff;
        border: 2px solid #1E3E62;
        border-radius: 10px;
        padding: .6rem 1.1rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        transition: all .15s ease;
    }

    .export-btn:hover {
        background: #16304e;
        color: #fff;
    }
</style>
@endpush

@section('content')
@php
    $routePrefix = request()->routeIs('petugas.*') ? 'petugas' : 'admin';
    $canEditData = auth()->user()?->isAdmin() || auth()->user()?->isPetugas();
    $isAdmin = auth()->user()?->isAdmin();
    $activeFilter = $filter ?? request('filter', 'harian');
    $isSpecialReport = ($isPetugasReport ?? false) || ($isKepalaReport ?? false);

    // TPU dropdown ditampilkan untuk: admin di laporan biasa, ATAU Kepala TPU / KDLH
    // (yang laporannya memang mencakup seluruh TPU).
    $showTpuFilter = (! $isSpecialReport && $isAdmin) || ($isKepalaReport ?? false);

    // Warna aksen per "tone" kartu statistik, dipakai lewat foreach di bawah
    // supaya markup kartu cukup ditulis SATU kali untuk semua kasus.
    $statTones = [
        'accent'  => ['border' => '#1E3E62', 'text' => '#1E3E62'],
        'success' => ['border' => '#027a48', 'text' => '#027a48'],
        'warning' => ['border' => '#b45309', 'text' => '#b45309'],
        'danger'  => ['border' => '#dc2626', 'text' => '#dc2626'],
        'neutral' => ['border' => '#98a2b3', 'text' => '#475467'],
    ];

    // Susun konfigurasi kartu statistik berdasarkan jenis laporan yang aktif.
    // Setiap grup punya 'title' (null jika tidak perlu judul) dan daftar 'cards'.
    if ($isKepalaReport ?? false) {
        $statGroups = [
            [
                'title' => 'Ringkasan Pemakaman',
                'cards' => [
                    ['icon' => 'bi-clipboard-data', 'label' => 'Total Pemakaman', 'value' => $totalPemakaman ?? 0, 'tone' => 'accent'],
                    ['icon' => 'bi-file-earmark-text', 'label' => 'Permohonan Masuk', 'value' => $totalPermohonan ?? 0, 'tone' => 'accent'],
                    ['icon' => 'bi-gender-male', 'label' => 'Laki-laki', 'value' => $laki ?? 0, 'tone' => 'neutral'],
                    ['icon' => 'bi-gender-female', 'label' => 'Perempuan', 'value' => $perempuan ?? 0, 'tone' => 'neutral'],
                ],
            ],
            [
                'title' => 'Ringkasan Permohonan',
                'cards' => [
                    ['icon' => 'bi-collection', 'label' => 'Total Data', 'value' => $total ?? 0, 'tone' => 'neutral'],
                    ['icon' => 'bi-hourglass-split', 'label' => 'Menunggu', 'value' => $permohonanMenunggu ?? 0, 'tone' => 'warning'],
                    ['icon' => 'bi-check-circle', 'label' => 'Disetujui', 'value' => $permohonanDisetujui ?? 0, 'tone' => 'success'],
                    ['icon' => 'bi-x-circle', 'label' => 'Ditolak', 'value' => $permohonanDitolak ?? 0, 'tone' => 'danger'],
                ],
            ],
        ];
    } elseif ($isPetugasReport ?? false) {
        $statGroups = [
            [
                'title' => null,
                'cards' => [
                    ['icon' => 'bi-collection', 'label' => 'Total Data', 'value' => $total ?? 0, 'tone' => 'accent'],
                    ['icon' => 'bi-file-earmark-text', 'label' => 'Permohonan Masuk', 'value' => $totalPermohonan ?? 0, 'tone' => 'neutral'],
                    ['icon' => 'bi-person-vcard', 'label' => 'Data Jenazah', 'value' => $totalJenazah ?? 0, 'tone' => 'neutral'],
                    ['icon' => 'bi-map-fill', 'label' => 'Makam Terhubung', 'value' => $totalMakamTerhubung ?? 0, 'tone' => 'success'],
                ],
            ],
        ];
    } else {
        $statGroups = [
            [
                'title' => null,
                'cards' => [
                    ['icon' => 'bi-clipboard-data', 'label' => 'Total Pemakaman', 'value' => $total ?? 0, 'tone' => 'accent'],
                    ['icon' => 'bi-gender-male', 'label' => 'Laki-laki', 'value' => $laki ?? 0, 'tone' => 'neutral'],
                    ['icon' => 'bi-gender-female', 'label' => 'Perempuan', 'value' => $perempuan ?? 0, 'tone' => 'neutral'],
                    ['icon' => 'bi-map-fill', 'label' => 'Makam Terhubung', 'value' => $totalMakamTerisi ?? 0, 'tone' => 'success'],
                ],
            ],
        ];
    }
@endphp

<div class="container-fluid pt-2 pb-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                @if($isKepalaReport ?? false)
                    Laporan Kepala TPU
                @elseif($isPetugasReport ?? false)
                    Laporan Petugas TPU
                @else
                    Laporan Pemakaman
                @endif
            </h4>
            <p class="text-muted mb-0">
                @if($isKepalaReport ?? false)
                    Gabungan data pemakaman, permohonan, dan jenazah untuk seluruh TPU
                @elseif($isPetugasReport ?? false)
                    Gabungan data permohonan dan data jenazah untuk {{ auth()->user()->tpu }}
                @else
                    Rekap data pemakaman berdasarkan periode waktu
                @endif
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge px-3 py-2" style="background-color: #1E3E62; color: white;">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
            @if(! empty($wordRoute))
                <a href="{{ $wordRoute }}" class="export-btn">
                    <i class="bi bi-file-earmark-word"></i> Export Word
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Quick filter chips --}}
    <div class="d-flex gap-2 flex-wrap mb-3" id="quickFilterChips">
        <button type="button" class="quick-filter-chip" data-range="today">Hari Ini</button>
        <button type="button" class="quick-filter-chip" data-range="week">Minggu Ini</button>
        <button type="button" class="quick-filter-chip" data-range="month">Bulan Ini</button>
        <button type="button" class="quick-filter-chip" data-range="year">Tahun Ini</button>
    </div>

    {{-- Filter form (satu bentuk untuk semua kondisi; kolom TPU muncul hanya untuk admin di laporan biasa) --}}
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ request()->url() }}" id="filterForm">
                <div class="row g-3 align-items-end">
                    @if($showTpuFilter)
                        <div class="col-md-3">
                            <label class="form-label">TPU</label>
                            <select name="tpu" class="form-select">
                                <option value="">Semua TPU</option>
                                @foreach($tpuOptions ?? [] as $tpu)
                                    <option value="{{ $tpu }}" @selected(($selectedTpu ?? '') === $tpu)>{{ $tpu }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- <div class="col-md-3">
                        <label class="form-label">Jenis Laporan</label>
                        <select name="filter" class="form-select">
                            <option value="harian" @selected($activeFilter === 'harian')>Harian</option>
                            <option value="mingguan" @selected($activeFilter === 'mingguan')>Mingguan</option>
                            <option value="bulanan" @selected($activeFilter === 'bulanan')>Bulanan</option>
                            <option value="tahunan" @selected($activeFilter === 'tahunan')>Tahunan</option>
                        </select>
                    </div> -->

                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start" id="filterStart" value="{{ $start ?? request('start') }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="end" id="filterEnd" value="{{ $end ?? request('end') }}" class="form-control">
                    </div>

                    <div class="col-md-3 offset-md-{{ $showTpuFilter ? 0 : 3 }}">
                        <button class="btn w-100" style="background-color:#1E3E62;color:white;">
                            <i class="bi bi-filter"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Statistik: satu blok markup, datanya beda-beda lewat $statGroups --}}
    @foreach($statGroups as $group)
        @if($group['title'])
            <p class="stat-group-title">{{ $group['title'] }}</p>
        @endif
        <div class="row g-3 mb-4">
            @foreach($group['cards'] as $card)
                @php $tone = $statTones[$card['tone']]; @endphp
                <div class="col-6 col-md-3">
                    <div class="stat-card" style="border-left-color: {{ $tone['border'] }};">
                        <i class="bi {{ $card['icon'] }}" style="color: {{ $tone['text'] }};"></i>
                        <p class="stat-card-label">{{ $card['label'] }}</p>
                        <h4 class="stat-card-value">{{ $card['value'] }}</h4>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

    {{-- Tabel: satu blok markup untuk semua jenis laporan --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h6 class="fw-bold mb-0">
                {{ $isSpecialReport ? 'Data Pemakaman Gabungan' : 'Data Pemakaman' }}
            </h6>
            <small class="text-muted">
                {{ $isSpecialReport ? 'Memuat data permohonan dan data jenazah yang masuk untuk TPU ini' : 'Hasil laporan berdasarkan filter' }}
            </small>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-bordered align-middle laporan-table">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Sumber</th>
                            <th>Jenazah</th>
                            <th>JK</th>
                            <th>Tgl Input</th>
                            <th>Tgl Wafat</th>
                            <th>Kode Makam</th>
                            <th>Lokasi</th>
                            <th>Status Permohonan</th>
                            <th>Status Makam</th>
                            <!-- <th>Catatan</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportRows ?? [] as $row)
                            @php
                                $lokasi = trim(implode(' / ', array_filter([
                                    $row['blok'] ?? null,
                                    $row['zona'] ?? null,
                                    ! empty($row['nomor_makam'] ?? null) ? 'No ' . $row['nomor_makam'] : null,
                                ])), ' /');
                                $catatanTeks = ($row['source'] === 'permohonan' && ($row['nama_ahli_waris'] ?? '-') !== '-')
                                    ? 'Ahli waris: ' . $row['nama_ahli_waris'] . ' | ' . ($row['catatan'] ?? '')
                                    : ($row['catatan'] ?? '');
                                $statusMakam = $row['status_makam'] ?? null;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge {{ $row['source'] === 'permohonan' ? 'bg-primary' : 'bg-success' }}">
                                        {{ $row['source_label'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $row['nama'] }}</div>
                                    <small class="text-muted">{{ $row['nik'] }}</small>
                                </td>
                                <td>{{ $row['jenis_kelamin'] }}</td>
                                <td>{{ $row['tanggal_input_label'] }}</td>
                                <td>{{ $row['tanggal_wafat_label'] }}</td>
                                <td>{{ $row['kode_makam'] }}</td>
                                <td>{{ $lokasi !== '' ? $lokasi : '-' }}</td>
                                <td>
                                    @if($row['source'] === 'permohonan')
                                        <span class="badge {{ $row['status_permohonan'] === 'disetujui' ? 'bg-success' : ($row['status_permohonan'] === 'ditolak' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                            {{ ucfirst($row['status_permohonan'] ?? '-') }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $statusMakam === 'kosong' ? 'bg-secondary' : ($statusMakam ? 'bg-success' : 'bg-warning text-dark') }}">
                                        {{ $statusMakam ? ucfirst($statusMakam) : 'Belum Ada' }}
                                    </span>
                                </td>
                                <!-- <td>
                                    <small class="laporan-catatan" title="{{ $catatanTeks }}">{{ $catatanTeks !== '' ? $catatanTeks : '-' }}</small>
                                </td> -->
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Tidak ada data pada rentang tanggal ini. Coba ubah filter di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if(($reportRows ?? null) instanceof \Illuminate\Contracts\Pagination\Paginator && $reportRows->hasPages())
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top">
                        <small class="text-muted">
                            Menampilkan {{ $reportRows->firstItem() }} - {{ $reportRows->lastItem() }} dari {{ $reportRows->total() }} data
                        </small>
                        {{ $reportRows->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var chipsWrap = document.getElementById('quickFilterChips');
        var startInput = document.getElementById('filterStart');
        var endInput = document.getElementById('filterEnd');
        var form = document.getElementById('filterForm');

        if (! chipsWrap || ! startInput || ! endInput || ! form) {
            return;
        }

        function pad(n) {
            return n < 10 ? '0' + n : '' + n;
        }

        function toIsoDate(d) {
            return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
        }

        function applyRange(range) {
            var now = new Date();
            var start = new Date(now);
            var end = new Date(now);

            if (range === 'week') {
                var day = (now.getDay() + 6) % 7;
                start.setDate(now.getDate() - day);
            } else if (range === 'month') {
                start = new Date(now.getFullYear(), now.getMonth(), 1);
            } else if (range === 'year') {
                start = new Date(now.getFullYear(), 0, 1);
            }

            startInput.value = toIsoDate(start);
            endInput.value = toIsoDate(end);
            form.submit();
        }

        chipsWrap.querySelectorAll('.quick-filter-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                chipsWrap.querySelectorAll('.quick-filter-chip').forEach(function (c) {
                    c.classList.remove('active');
                });
                chip.classList.add('active');
                applyRange(chip.getAttribute('data-range'));
            });
        });
    })();
</script>
@endpush