@extends('admin.layouts.app')

@section('title', 'Dashboard Admin')

@section('content')

@php
    $role = auth()->user()->role ?? 'admin';
@endphp

<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                @if($role == 'admin')
                    Dashboard Admin
                @elseif($role == 'petugas')
                    Dashboard Petugas
                @elseif($role == 'kepala')
                    Dashboard Kepala UPT
                @else
                    Dashboard
                @endif
            </h4>
            <p class="text-muted mb-0">Ringkasan informasi pengelolaan Sistem Informasi Pemakaman</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="badge px-3 py-2" style="background-color: #1E3E62; color: white;">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
    </div>

    {{-- STATISTIK --}}
    <div class="row g-4 mb-4">

        {{-- SELALU ADA --}}
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Jenazah</p>
                        <h4 class="fw-bold mb-0">{{ $totalJenazah ?? 0 }}</h4>
                        <small class="text-muted">Data jenazah</small>
                    </div>
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Makam</p>
                        <h4 class="fw-bold mb-0">{{ $totalMakam ?? 0 }}</h4>
                        <small class="text-muted">Makam aktif</small>
                    </div>
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Permohonan</p>
                        <h4 class="fw-bold mb-0">{{ $totalPermohonan ?? 0 }}</h4>
                        <small class="text-muted">Seluruh data sistem</small>
                    </div>
                    <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                        <i class="bi bi-envelope-paper-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- KHUSUS KEPALA --}}
        @if($role == 'kepala')
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Permohonan Masuk</p>
                        <h4 class="fw-bold mb-0">{{ $totalPermohonan ?? 0 }}</h4>
                        <small class="text-muted">Total permohonan</small>
                    </div>
                    <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                        <i class="bi bi-envelope-paper-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Menunggu Verifikasi</p>
                        <h4 class="fw-bold mb-0">{{ $permohonanPending ?? 0 }}</h4>
                        <small class="text-muted">Belum diproses</small>
                    </div>
                    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0">Ringkasan Per TPU</h6>
                <small class="text-muted">Total jenazah, makam, dan permohonan dari masing-masing TPU.</small>
            </div>
        </div>
        <div class="card-body px-4">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>TPU</th>
                            <th>Total Jenazah</th>
                            <th>Total Makam</th>
                            <th>Total Permohonan</th>
                            <th>Menunggu</th>
                            <th>Disetujui</th>
                            <th>Ditolak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($perTpuStats ?? [] as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item['tpu'] }}</td>
                            <td>{{ $item['totalJenazah'] }}</td>
                            <td>{{ $item['totalMakam'] }}</td>
                            <td>{{ $item['totalPermohonan'] }}</td>
                            <td>{{ $item['permohonanPending'] }}</td>
                            <td class="text-success">{{ $item['permohonanDisetujui'] }}</td>
                            <td class="text-danger">{{ $item['permohonanDitolak'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada data TPU</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- CHART + INFO --}}
    <div class="row g-4 mb-4">

        {{-- CHART --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <h6 class="fw-bold mb-0">Grafik Permohonan</h6>
                </div>
                <div class="card-body px-4">
                    <canvas id="permohonanChart"></canvas>
                </div>
            </div>
        </div>

        {{-- INFO --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <h6 class="fw-bold mb-0">Informasi Sistem</h6>
                </div>
                <div class="card-body px-4">

                    <ul class="list-group list-group-flush">

                        @if($role == 'kepala')
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Jumlah Petugas</span>
                            <strong>{{ $totalPetugas ?? 0 }}</strong>
                        </li>

                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Jumlah Pengguna</span>
                            <strong>{{ $totalUser ?? 0 }}</strong>
                        </li>
                        @endif

                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Disetujui</span>
                            <strong class="text-success">{{ $permohonanDisetujui ?? 0 }}</strong>
                        </li>

                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span>Ditolak</span>
                            <strong class="text-danger">{{ $permohonanDitolak ?? 0 }}</strong>
                        </li>

                    </ul>

                    <div class="mt-4">
                        @if($role == 'admin' || $role == 'petugas')
                            <a href="#" class="btn btn-primary w-100" style="background:#1E3E62;color:white;">
                                Proses Permohonan
                            </a>
                        @else
                            <a href="#" class="btn btn-success w-100">
                                Lihat Laporan
                            </a>
                        @endif
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- TABEL --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h6 class="fw-bold mb-0">Permohonan Terbaru</h6>
        </div>

        <div class="card-body px-4">

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Jenis</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($permohonanTerbaru ?? [] as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->nama_pemohon }}</td>
                            <td>{{ $item->jenis_permohonan }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Tidak ada data
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('permohonanChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartLabels ?? ['Jan','Feb','Mar']) !!},
        datasets: [{
            label: 'Permohonan',
            data: {!! json_encode($chartData ?? [0,0,0]) !!}
        }]
    }
});
</script>
@endpush
