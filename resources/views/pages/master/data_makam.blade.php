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

    {{-- Tabel Data Makam --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h6 class="fw-bold mb-0">Data Makam</h6>
            <small class="text-muted">Daftar Data Makam yang masuk ke sistem</small>
        </div>

        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode Makam</th>
                            <th>Blok</th>
                            <th>Zona</th>
                            <th>Nomor Makam</th>
                            <th>Status</th>
                            <th>Katerangan</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Makam 1</td>
                            <td>A</td>
                            <td>Nyaman</td>
                            <td>201</td>
                            <th><span class="badge bg-success">Disetujui</span></th>
                            <td>Bahlil</td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')

@endpushs