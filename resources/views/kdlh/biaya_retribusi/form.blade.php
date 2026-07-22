@extends('admin.layouts.app')

@section('title', $biayaRetribusi->exists ? 'Edit Biaya Retribusi' : 'Tambah Biaya Retribusi')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">{{ $biayaRetribusi->exists ? 'Edit Biaya Retribusi' : 'Tambah Biaya Retribusi' }}</h4>
            <p class="text-muted mb-0">Atur biaya retribusi yang berlaku untuk seluruh TPU.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ $biayaRetribusi->exists ? route('kdlh.biaya-retribusi.update', $biayaRetribusi) : route('kdlh.biaya-retribusi.store') }}">
                @csrf
                @if($biayaRetribusi->exists)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama / Kategori Biaya</label>
                        <input type="text" name="nama_biaya" class="form-control" value="{{ old('nama_biaya', $biayaRetribusi->nama_biaya) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nominal</label>
                        <input type="number" min="0" step="1" name="nominal" class="form-control" value="{{ old('nominal', $biayaRetribusi->nominal) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nomor Rekening Tujuan</label>
                        <input type="text" name="nomor_rekening" class="form-control" value="{{ old('nomor_rekening', $biayaRetribusi->nomor_rekening) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nama Bank</label>
                        <input type="text" name="nama_bank" class="form-control" value="{{ old('nama_bank', $biayaRetribusi->nama_bank) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Atas Nama Rekening</label>
                        <input type="text" name="atas_nama_rekening" class="form-control" value="{{ old('atas_nama_rekening', $biayaRetribusi->atas_nama_rekening) }}">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_aktif" value="1" id="is_aktif" @checked(old('is_aktif', $biayaRetribusi->is_aktif ?? true))>
                            <label class="form-check-label" for="is_aktif">
                                Aktifkan biaya retribusi ini
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('kdlh.biaya-retribusi.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary" style="background:#1E3E62;border-color:#1E3E62;">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
