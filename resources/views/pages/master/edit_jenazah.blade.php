@extends('admin.layouts.app')

@section('title', 'Edit Data Jenazah')

@section('content')
@php
    $routePrefix = request()->routeIs('petugas.*') ? 'petugas' : 'admin';
@endphp

<div class="container-fluid pt-2 pb-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Edit Data Jenazah</h4>
            <p class="text-muted mb-0">Perbarui informasi data jenazah</p>
        </div>
        <div>
            <span class="badge px-3 py-2" style="background-color: #1E3E62; color: white;">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
    </div>

    {{-- FORM --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body px-4 py-4">

            <form action="{{ route($routePrefix.'.data-jenazah.update', $jenazah->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">

                    {{-- NIK --}}
                    <div class="col-md-6">
                        <label class="form-label">NIK</label>
                        <input type="text" name="nik"
                               class="form-control @error('nik') is-invalid @enderror"
                               value="{{ old('nik', $jenazah->nik) }}"
                               placeholder="Masukkan NIK">

                        @error('nik')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nama --}}
                    <div class="col-md-6">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama"
                               class="form-control @error('nama') is-invalid @enderror"
                               value="{{ old('nama', $jenazah->nama) }}"
                               placeholder="Masukkan nama">

                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div class="col-md-6">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin"
                                class="form-select @error('jenis_kelamin') is-invalid @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki"
                                {{ old('jenis_kelamin', $jenazah->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>
                                Laki-laki
                            </option>
                            <option value="Perempuan"
                                {{ old('jenis_kelamin', $jenazah->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>
                                Perempuan
                            </option>
                        </select>

                        @error('jenis_kelamin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tanggal Wafat --}}
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Wafat</label>
                        <input type="date" name="tanggal_wafat"
                               class="form-control @error('tanggal_wafat') is-invalid @enderror"
                               value="{{ old('tanggal_wafat', $jenazah->tanggal_wafat) }}">

                        @error('tanggal_wafat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Alamat --}}
                    <div class="col-12">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat"
                                  class="form-control @error('alamat') is-invalid @enderror"
                                  rows="3"
                                  placeholder="Masukkan alamat lengkap">{{ old('alamat', $jenazah->alamat) }}</textarea>

                        @error('alamat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                {{-- BUTTON --}}
                <div class="d-flex justify-content-between mt-4">

                    <a href="{{ route($routePrefix.'.data-jenazah') }}"
                       class="btn btn-outline-secondary rounded-3">
                        ← Kembali
                    </a>

                    <button type="submit"
                            class="btn rounded-3"
                            style="background-color:#1E3E62; color:white;">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>
    </div>

</div>
@endsection

@push('scripts')
@endpush
