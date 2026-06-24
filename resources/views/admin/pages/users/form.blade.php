@extends('admin.layouts.app')

@php
    $isEdit = $user->exists;
    $routePrefix = request()->routeIs('kepala.*') ? 'kepala' : (request()->routeIs('kdlh.*') ? 'kdlh' : 'admin');
    $isKepalaRoute = request()->routeIs('kepala.*');
    $isKdlhRoute = request()->routeIs('kdlh.*');
@endphp

@section('title', $isEdit ? 'Edit User' : 'Tambah User')

@section('content')
<div class="container-fluid pt-2 pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">{{ $isEdit ? 'Edit User' : 'Tambah User' }}</h4>
            <p class="text-muted mb-0">{{ $isKepalaRoute ? 'Atur akun petugas TPU.' : ($isKdlhRoute ? 'Atur akun kepala TPU.' : 'Atur akun dan role pengguna sistem.') }}</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body px-4 py-4">
            <form action="{{ $isEdit ? route($routePrefix.'.users.update', $user) : route($routePrefix.'.users.store') }}" method="POST">
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
                        <select name="role" class="form-select @error('role') is-invalid @enderror" required {{ ($isKepalaRoute || $isKdlhRoute) ? 'disabled' : '' }}>
                            @if($isKepalaRoute)
                                <option value="petugas" @selected(old('role', $user->role ?: 'petugas') === 'petugas')>Petugas TPU</option>
                            @elseif($isKdlhRoute)
                                <option value="kepala" @selected(old('role', $user->role ?: 'kepala') === 'kepala')>Kepala UPT</option>
                            @else
                                <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                                <option value="kdlh" @selected(old('role', $user->role) === 'kdlh')>Kepala Dinas Lingkungan Hidup</option>
                                <option value="petugas" @selected(old('role', $user->role) === 'petugas')>Petugas TPU</option>
                                <option value="kepala" @selected(old('role', $user->role) === 'kepala')>Kepala UPT</option>
                                <option value="user" @selected(old('role', $user->role ?: 'user') === 'user')>Ahli Waris</option>
                            @endif
                        </select>
                        @if($isKepalaRoute || $isKdlhRoute)
                            <input type="hidden" name="role" value="{{ $isKdlhRoute ? 'kepala' : 'petugas' }}">
                        @endif
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">TPU Petugas</label>
                        <select name="tpu" class="form-select @error('tpu') is-invalid @enderror" {{ ($isKepalaRoute || $isKdlhRoute) ? 'required' : '' }}>
                            <option value="">Pilih TPU jika role petugas</option>
                            @foreach($tpuOptions ?? [] as $tpu)
                                <option value="{{ $tpu }}" @selected(old('tpu', $user->tpu) === $tpu)>{{ $tpu }}</option>
                            @endforeach
                        </select>
                        @if($isKepalaRoute || $isKdlhRoute)
                            <small class="text-muted d-block mt-1">{{ $isKdlhRoute ? 'Kepala dinas hanya dapat menambah akun kepala TPU.' : 'Kepala TPU hanya dapat menambah akun petugas TPU.' }}</small>
                        @endif
                        @error('tpu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn" style="background-color:#1E3E62;color:white;">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
