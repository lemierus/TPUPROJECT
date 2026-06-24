@extends('admin.layouts.app')

@section('title', $tpu->exists ? 'Edit TPU' : 'Tambah TPU')

@section('content')
<div class="container-fluid pt-2 pb-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <div class="mb-4">
                <h4 class="fw-bold mb-1">{{ $tpu->exists ? 'Edit TPU' : 'Tambah TPU' }}</h4>
                <p class="text-muted mb-0">Isi data TPU yang akan tampil di seluruh sistem.</p>
            </div>

            <form method="POST" action="{{ $tpu->exists ? route('kdlh.tpu.update', $tpu) : route('kdlh.tpu.store') }}">
                @csrf
                @if($tpu->exists)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama TPU</label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $tpu->nama) }}" required>
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Lokasi</label>
                        <input type="text" name="lokasi" class="form-control @error('lokasi') is-invalid @enderror" value="{{ old('lokasi', $tpu->lokasi) }}">
                        @error('lokasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Ringkasan</label>
                        <textarea name="ringkasan" class="form-control @error('ringkasan') is-invalid @enderror" rows="3">{{ old('ringkasan', $tpu->ringkasan) }}</textarea>
                        @error('ringkasan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Highlight</label>
                        <textarea name="highlight" class="form-control @error('highlight') is-invalid @enderror" rows="3">{{ old('highlight', $tpu->highlight) }}</textarea>
                        @error('highlight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="4">{{ old('deskripsi', $tpu->deskripsi) }}</textarea>
                        @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('kdlh.tpu.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">{{ $tpu->exists ? 'Simpan Perubahan' : 'Simpan TPU' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
