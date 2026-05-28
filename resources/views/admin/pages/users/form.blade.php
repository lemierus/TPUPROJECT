@extends('admin.layouts.app')

@php
    $isEdit = $user->exists;
@endphp

@section('title', $isEdit ? 'Edit User' : 'Tambah User')

@section('content')
<div class="container-fluid pt-2 pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">{{ $isEdit ? 'Edit User' : 'Tambah User' }}</h4>
            <p class="text-muted mb-0">Atur akun dan role pengguna sistem.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body px-4 py-4">
            <form action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Password {{ $isEdit ? '(kosongkan jika tidak diganti)' : '' }}</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ $isEdit ? '' : 'required' }}>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                            <option value="petugas" @selected(old('role', $user->role) === 'petugas')>Petugas TPU</option>
                            <option value="kepala" @selected(old('role', $user->role) === 'kepala')>Kepala UPT</option>
                            <option value="user" @selected(old('role', $user->role ?: 'user') === 'user')>Ahli Waris</option>
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">TPU Petugas</label>
                        <select name="tpu" class="form-select @error('tpu') is-invalid @enderror">
                            <option value="">Pilih TPU jika role petugas</option>
                            <option value="TPU Tunggul Hitam" @selected(old('tpu', $user->tpu) === 'TPU Tunggul Hitam')>TPU Tunggul Hitam</option>
                            <option value="TPU Bungus Teluk Kabung" @selected(old('tpu', $user->tpu) === 'TPU Bungus Teluk Kabung')>TPU Bungus Teluk Kabung</option>
                            <option value="TPU Air Dingin" @selected(old('tpu', $user->tpu) === 'TPU Air Dingin')>TPU Air Dingin</option>
                        </select>
                        @error('tpu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Kembali</a>
                    <button type="submit" class="btn" style="background-color:#1E3E62;color:white;">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
