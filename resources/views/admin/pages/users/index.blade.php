@extends('admin.layouts.app')

@section('title', 'Data User')

@section('content')
@php
    $routePrefix = request()->routeIs('kepala.*') ? 'kepala' : (request()->routeIs('kdlh.*') ? 'kdlh' : 'admin');
    $isKepalaRoute = request()->routeIs('kepala.*');
    $isKdlhRoute = request()->routeIs('kdlh.*');
@endphp
<div class="container-fluid pt-2 pb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Data User</h4>
            <p class="text-muted mb-0">{{ $isKepalaRoute ? 'Kelola akun petugas TPU.' : ($isKdlhRoute ? 'Kelola akun kepala TPU.' : 'Kelola akun admin, kepala UPT, dan ahli waris.') }}</p>
        </div>
        <a href="{{ route($routePrefix.'.users.create') }}" class="btn btn-sm" style="background-color:#1E3E62;color:white;">
            <i class="bi bi-plus-circle"></i> {{ $isKepalaRoute ? 'Tambah Petugas' : ($isKdlhRoute ? 'Tambah Kepala TPU' : 'Tambah User') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route($routePrefix.'.users.index') }}">
                <div class="row g-2">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau role..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn w-100" style="background-color:#1E3E62;color:white;">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>NIP</th>
                            <th>No. HP</th>
                            <th>Role</th>
                            <th>TPU</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->nip ?? '-' }}</td>
                                <td>{{ $user->no_hp ?? '-' }}</td>
                                <td><span class="badge bg-secondary">{{ strtoupper($user->role) }}</span></td>
                                <td>{{ $user->tpu ?? '-' }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route($routePrefix.'.users.edit', $user) }}" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <form action="{{ route($routePrefix.'.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Yakin hapus user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Data user tidak ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($users instanceof \Illuminate\Contracts\Pagination\Paginator && $users->hasPages())
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top">
                        <small class="text-muted">
                            Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }} data
                        </small>
                        {{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection