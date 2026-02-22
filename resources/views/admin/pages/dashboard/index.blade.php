@extends('admin.layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Dashboard Petugas</h4>
            <p class="text-muted mb-0">Ringkasan informasi pengelolaan Sistem Informasi Pemakaman</p>
        </div>
        <div>
            <span class="badge px-3 py-2" style="background-color: #1E3E62 !important; color: white;">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>

        </div>
    </div>

    {{-- Statistik Card --}}
    <div class="row g-4 mb-4">

        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Jenazah</p>
                            <h4 class="fw-bold mb-0">{{ $totalJenazah ?? 0 }}</h4>
                        </div>
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 50px; height: 50px;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">Jumlah data jenazah terdaftar</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Makam</p>
                            <h4 class="fw-bold mb-0">{{ $totalMakam ?? 0 }}</h4>
                        </div>
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 50px; height: 50px;">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">Jumlah makam aktif dalam sistem</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Permohonan Masuk</p>
                            <h4 class="fw-bold mb-0">{{ $totalPermohonan ?? 0 }}</h4>
                        </div>
                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 50px; height: 50px;">
                            <i class="bi bi-envelope-paper-fill"></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">Total permohonan dari masyarakat</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Menunggu Verifikasi</p>
                            <h4 class="fw-bold mb-0">{{ $permohonanPending ?? 0 }}</h4>
                        </div>
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 50px; height: 50px;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">Permohonan belum diproses</small>
                </div>
            </div>
        </div>

    </div>

    {{-- Chart dan Aktivitas --}}
    <div class="row g-4 mb-4">

        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <h6 class="fw-bold mb-0">Grafik Permohonan per Bulan</h6>
                    <small class="text-muted">Statistik jumlah permohonan masuk berdasarkan bulan</small>
                </div>
                <div class="card-body px-4 pb-4">
                    <canvas id="permohonanChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <h6 class="fw-bold mb-0">Informasi Sistem</h6>
                    <small class="text-muted">Status sistem dan data ringkas</small>
                </div>
                <div class="card-body px-4 pb-4">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Jumlah Petugas</span>
                            <span class="fw-bold">{{ $totalPetugas ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Jumlah Pengguna</span>
                            <span class="fw-bold">{{ $totalUser ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Permohonan Disetujui</span>
                            <span class="fw-bold text-success">{{ $permohonanDisetujui ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Permohonan Ditolak</span>
                            <span class="fw-bold text-danger">{{ $permohonanDitolak ?? 0 }}</span>
                        </li>
                    </ul>
                    <div class="mt-4">
                        <a href="#" class="btn btn-primary w-100 rounded-3" style="background-color: #1E3E62 !important; color: white;">
                            Kelola Data Permohonan
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Tabel Permohonan Terbaru --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h6 class="fw-bold mb-0">Permohonan Terbaru</h6>
            <small class="text-muted">Daftar permohonan terbaru yang masuk ke sistem</small>
        </div>

        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Pemohon</th>
                            <th>Jenis Permohonan</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permohonanTerbaru ?? [] as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nama_pemohon ?? '-' }}</td>
                                <td>{{ $item->jenis_permohonan ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                                <td>
                                    @if($item->status == 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($item->status == 'disetujui')
                                        <span class="badge bg-success">Disetujui</span>
                                    @elseif($item->status == 'ditolak')
                                        <span class="badge bg-danger">Ditolak</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Diketahui</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-primary">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Belum ada permohonan terbaru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <a href="#" class="btn btn-outline-secondary rounded-3">
                    Lihat Semua Permohonan
                </a>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('permohonanChart');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels ?? ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']) !!},
            datasets: [{
                label: 'Jumlah Permohonan',
                data: {!! json_encode($chartData ?? [0,0,0,0,0,0,0,0,0,0,0,0]) !!},
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush
