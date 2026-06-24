@extends('admin.layouts.app')

@section('title', 'Kelola TPU')

@section('content')
<div class="container-fluid pt-2 pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Kelola TPU</h4>
            <p class="text-muted mb-0">Tambah dan ubah data deskripsi TPU yang dipakai di seluruh halaman sistem.</p>
        </div>
        <a href="{{ route('kdlh.tpu.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah TPU
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama TPU</th>
                            <th>Lokasi</th>
                            <th>Ringkasan</th>
                            <th>Deskripsi</th>
                            <th>Urutan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tpus as $tpu)
                            <tr>
                                <td class="fw-semibold">{{ $tpu->nama }}</td>
                                <td>{{ $tpu->lokasi ?? '-' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($tpu->ringkasan ?? '-', 70) }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($tpu->deskripsi ?? '-', 70) }}</td>
                                <td>{{ $tpu->urutan ?? 0 }}</td>
                                <td>
                                    <a href="{{ route('kdlh.tpu.edit', $tpu) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada data TPU</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
