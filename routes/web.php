<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Kdlh\DashboardController as KdlhDashboardController;
use App\Http\Controllers\Kdlh\TpuController as KdlhTpuController;
use App\Http\Controllers\Kepala\DashboardController as KepalaDashboardController;
use App\Http\Controllers\Master\DataJenazahController;
use App\Http\Controllers\Master\DataMakamController;
use App\Http\Controllers\Master\LaporanController;
use App\Http\Controllers\Master\PermohonanController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use App\Http\Controllers\Petugas\PermohonanController as PetugasPermohonanController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\PermohonanController as UserPermohonanController;
use App\Models\Tpu;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $landingTpus = Tpu::query()->orderBy('urutan')->orderBy('nama')->get()->map(function (Tpu $tpu) {
        return [
            'slug' => str()->slug($tpu->nama),
            'nama' => $tpu->nama,
            'lokasi' => $tpu->lokasi ?? '-',
            'ringkasan' => $tpu->ringkasan ?? '-',
            'highlight' => $tpu->highlight ?? '-',
        ];
    })->values()->all();

    return view('welcome', [
        'landingTpus' => $landingTpus,
    ]);
});

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'proses'])->name('login.proses');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'prosesRegister'])->name('register.proses');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user?->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user?->role === 'petugas') {
            return redirect()->route('petugas.dashboard');
        }

        if ($user?->role === 'kepala') {
            return redirect()->route('kepala.dashboard');
        }

        if ($user?->role === 'kdlh') {
            return redirect()->route('kdlh.dashboard');
        }

        if ($user?->role === 'user') {
            return redirect()->route('user.dashboard');
        }

        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->with('error', 'Role akun tidak valid. Silakan login ulang.');
    })->name('dashboard');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', UserController::class)->except('show');

    Route::get('/data-jenazah', [DataJenazahController::class, 'index'])->name('data-jenazah');
    Route::get('/data-jenazah/create', [DataJenazahController::class, 'create'])->name('data-jenazah.create');
    Route::post('/data-jenazah', [DataJenazahController::class, 'store'])->name('data-jenazah.store');
    Route::get('/data-jenazah/{id}/edit', [DataJenazahController::class, 'edit'])->name('data-jenazah.edit');
    Route::put('/data-jenazah/{id}', [DataJenazahController::class, 'update'])->name('data-jenazah.update');
    Route::delete('/data-jenazah/{id}', [DataJenazahController::class, 'destroy'])->name('data-jenazah.destroy');

    Route::get('/data-makam', [DataMakamController::class, 'index'])->name('data-makam');
    Route::get('/data-makam/create', [DataMakamController::class, 'create'])->name('data-makam.create');
    Route::post('/data-makam', [DataMakamController::class, 'store'])->name('data-makam.store');
    Route::get('/data-makam/{makam}/edit', [DataMakamController::class, 'edit'])->name('data-makam.edit');
    Route::put('/data-makam/{makam}', [DataMakamController::class, 'update'])->name('data-makam.update');
    Route::delete('/data-makam/{makam}', [DataMakamController::class, 'destroy'])->name('data-makam.destroy');

    Route::get('/master/permohonan', [PermohonanController::class, 'index'])->name('master.permohonan');
    Route::get('/master/permohonan/create', [PermohonanController::class, 'create'])->name('master.permohonan.create');
    Route::post('/master/permohonan', [PermohonanController::class, 'store'])->name('master.permohonan.store');
    Route::get('/master/permohonan/{permohonan}/edit', [PermohonanController::class, 'edit'])->name('master.permohonan.edit');
    Route::put('/master/permohonan/{permohonan}', [PermohonanController::class, 'update'])->name('master.permohonan.update-data');
    Route::post('/master/permohonan/{id}/status', [PermohonanController::class, 'updateStatus'])->name('master.permohonan.update');
    Route::delete('/master/permohonan/{permohonan}', [PermohonanController::class, 'destroy'])->name('master.permohonan.destroy');

    Route::get('/master/laporan', [LaporanController::class, 'index'])->name('master.laporan');
    Route::get('/master/laporan/word', [LaporanController::class, 'word'])->name('master.laporan.word');
    Route::get('/master/laporan/print', [LaporanController::class, 'print'])->name('master.laporan.print');
    Route::get('/master/laporan/excel', [LaporanController::class, 'excel'])->name('master.laporan.excel');
    Route::get('/master/laporan/create', [LaporanController::class, 'create'])->name('master.laporan.create');
    Route::post('/master/laporan', [LaporanController::class, 'store'])->name('master.laporan.store');
    Route::get('/master/laporan/{laporan}/edit', [LaporanController::class, 'edit'])->name('master.laporan.edit');
    Route::put('/master/laporan/{laporan}', [LaporanController::class, 'update'])->name('master.laporan.update');
    Route::delete('/master/laporan/{laporan}', [LaporanController::class, 'destroy'])->name('master.laporan.destroy');
});

Route::middleware(['auth', 'petugas'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');

    Route::get('/permohonan', [PetugasPermohonanController::class, 'index'])->name('permohonan');
    Route::get('/permohonan/create', [PetugasPermohonanController::class, 'create'])->name('permohonan.create');
    Route::post('/permohonan', [PetugasPermohonanController::class, 'store'])->name('permohonan.store');
    Route::get('/permohonan/{permohonan}', [PetugasPermohonanController::class, 'show'])->name('permohonan.show');
    Route::get('/permohonan/{permohonan}/edit', [PetugasPermohonanController::class, 'edit'])->name('permohonan.edit');
    Route::put('/permohonan/{permohonan}', [PetugasPermohonanController::class, 'update'])->name('permohonan.update');
    Route::post('/permohonan/{permohonan}/approve', [PetugasPermohonanController::class, 'approve'])->name('permohonan.approve');
    Route::post('/permohonan/{permohonan}/reject', [PetugasPermohonanController::class, 'reject'])->name('permohonan.reject');

    Route::get('/data-jenazah', [DataJenazahController::class, 'index'])->name('data-jenazah');
    Route::get('/data-jenazah/create', [DataJenazahController::class, 'create'])->name('data-jenazah.create');
    Route::post('/data-jenazah', [DataJenazahController::class, 'store'])->name('data-jenazah.store');
    Route::get('/data-jenazah/{id}/edit', [DataJenazahController::class, 'edit'])->name('data-jenazah.edit');
    Route::put('/data-jenazah/{id}', [DataJenazahController::class, 'update'])->name('data-jenazah.update');
    Route::delete('/data-jenazah/{id}', [DataJenazahController::class, 'destroy'])->name('data-jenazah.destroy');

    Route::get('/data-makam', [DataMakamController::class, 'index'])->name('data-makam');
    Route::get('/data-makam/create', [DataMakamController::class, 'create'])->name('data-makam.create');
    Route::post('/data-makam', [DataMakamController::class, 'store'])->name('data-makam.store');
    Route::get('/data-makam/{makam}/edit', [DataMakamController::class, 'edit'])->name('data-makam.edit');
    Route::put('/data-makam/{makam}', [DataMakamController::class, 'update'])->name('data-makam.update');
    Route::delete('/data-makam/{makam}', [DataMakamController::class, 'destroy'])->name('data-makam.destroy');

    Route::get('/master/permohonan', [PermohonanController::class, 'index'])->name('master.permohonan');
    Route::get('/master/permohonan/create', [PermohonanController::class, 'create'])->name('master.permohonan.create');
    Route::post('/master/permohonan', [PermohonanController::class, 'store'])->name('master.permohonan.store');
    Route::get('/master/permohonan/{permohonan}/edit', [PermohonanController::class, 'edit'])->name('master.permohonan.edit');
    Route::put('/master/permohonan/{permohonan}', [PermohonanController::class, 'update'])->name('master.permohonan.update-data');
    Route::post('/master/permohonan/{id}/status', [PermohonanController::class, 'updateStatus'])->name('master.permohonan.update');
    Route::delete('/master/permohonan/{permohonan}', [PermohonanController::class, 'destroy'])->name('master.permohonan.destroy');

    Route::get('/master/laporan', [LaporanController::class, 'index'])->name('master.laporan');
    Route::get('/master/laporan/word', [LaporanController::class, 'word'])->name('master.laporan.word');
    Route::get('/master/laporan/print', [LaporanController::class, 'print'])->name('master.laporan.print');
    Route::get('/master/laporan/excel', [LaporanController::class, 'excel'])->name('master.laporan.excel');
    Route::get('/master/laporan/create', [LaporanController::class, 'create'])->name('master.laporan.create');
    Route::post('/master/laporan', [LaporanController::class, 'store'])->name('master.laporan.store');
    Route::get('/master/laporan/{laporan}/edit', [LaporanController::class, 'edit'])->name('master.laporan.edit');
    Route::put('/master/laporan/{laporan}', [LaporanController::class, 'update'])->name('master.laporan.update');
    Route::delete('/master/laporan/{laporan}', [LaporanController::class, 'destroy'])->name('master.laporan.destroy');
});

Route::middleware(['auth', 'kepala'])->prefix('kepala')->name('kepala.')->group(function () {
    Route::get('/dashboard', [KepalaDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class)->except('show');

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
    Route::get('/laporan/word', [LaporanController::class, 'word'])->name('laporan.word');
    Route::get('/laporan/print', [LaporanController::class, 'print'])->name('laporan.print');
    Route::get('/laporan/excel', [LaporanController::class, 'excel'])->name('laporan.excel');

    Route::get('/data-jenazah', [DataJenazahController::class, 'index'])->name('data-jenazah');
    Route::get('/data-makam', [DataMakamController::class, 'index'])->name('data-makam');
    Route::get('/data-makam/create', [DataMakamController::class, 'create'])->name('data-makam.create');
    Route::post('/data-makam', [DataMakamController::class, 'store'])->name('data-makam.store');
    Route::get('/data-makam/{makam}/edit', [DataMakamController::class, 'edit'])->name('data-makam.edit');
    Route::put('/data-makam/{makam}', [DataMakamController::class, 'update'])->name('data-makam.update');
    Route::delete('/data-makam/{makam}', [DataMakamController::class, 'destroy'])->name('data-makam.destroy');
});

Route::middleware(['auth', 'kdlh'])->prefix('kdlh')->name('kdlh.')->group(function () {
    Route::get('/dashboard', [KdlhDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class)->except('show');
    Route::resource('tpu', KdlhTpuController::class)->except(['show']);
    Route::get('/data-jenazah', [DataJenazahController::class, 'index'])->name('data-jenazah');
    Route::get('/data-makam', [DataMakamController::class, 'index'])->name('data-makam');
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
    Route::get('/laporan/word', [LaporanController::class, 'word'])->name('laporan.word');
});

Route::middleware(['auth', 'user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    Route::get('/permohonan/create', [UserPermohonanController::class, 'create'])->name('permohonan.create');
    Route::post('/permohonan', [UserPermohonanController::class, 'store'])->name('permohonan.store');
    Route::get('/permohonan/{permohonan}/ringkasan', [UserPermohonanController::class, 'summary'])->name('permohonan.summary');
    Route::get('/permohonan/{permohonan}/edit', [UserPermohonanController::class, 'edit'])->name('permohonan.edit');
    Route::put('/permohonan/{permohonan}', [UserPermohonanController::class, 'update'])->name('permohonan.update');
});

require __DIR__ . '/settings.php';
