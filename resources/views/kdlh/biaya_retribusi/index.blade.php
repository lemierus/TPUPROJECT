@extends('admin.layouts.app')

@section('title', 'Biaya Retribusi')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">Biaya Retribusi Makam</h4>
            <p class="text-muted mb-0">Kelola biaya retribusi yang berlaku untuk seluruh TPU.</p>
        </div>
        <a href="{{ route('kdlh.biaya-retribusi.create') }}" class="btn btn-primary" style="background:#1E3E62;border-color:#1E3E62;">
            <i class="bi bi-plus-circle me-1"></i> Tambah Biaya
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:70px;">No</th>
                            <th>Nama Biaya</th>
                            <th>Nominal</th>
                            <th>Rekening Tujuan</th>
                            <th>Status</th>
                            <th style="width:240px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($biayaRetribusis as $item)
                            <tr>
                                <td>{{ ($biayaRetribusis->firstItem() ?? 1) + $loop->index }}</td>
                                <td class="fw-semibold">{{ $item->nama_biaya }}</td>
                                <td>Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                <td>
                                    <div>{{ $item->nomor_rekening }}</div>
                                    <small class="text-muted">{{ $item->nama_bank }} a.n. {{ $item->atas_nama_rekening }}</small>
                                </td>
                                <td>
                                    @if($item->is_aktif)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('kdlh.biaya-retribusi.edit', $item) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <form action="{{ route('kdlh.biaya-retribusi.toggle', $item) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-arrow-repeat"></i> {{ $item->is_aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('kdlh.biaya-retribusi.destroy', $item) }}" method="POST" onsubmit="return confirm('Yakin hapus biaya retribusi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada data biaya retribusi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($biayaRetribusis->hasPages())
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-3 border-top">
                    <small class="text-muted">
                        Menampilkan {{ $biayaRetribusis->firstItem() }} - {{ $biayaRetribusis->lastItem() }} dari {{ $biayaRetribusis->total() }} data
                    </small>
                    {{ $biayaRetribusis->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
