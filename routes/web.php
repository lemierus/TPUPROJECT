<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Kepala\DashboardController as KepalaDashboardController;
use App\Http\Controllers\Master\DataJenazahController;
use App\Http\Controllers\Master\DataMakamController;
use App\Http\Controllers\Master\LaporanController;
use App\Http\Controllers\Master\PermohonanController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use App\Http\Controllers\Petugas\PermohonanController as PetugasPermohonanController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\PermohonanController as UserPermohonanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'landingTpus' => [
            [
                'slug' => 'tunggul-hitam',
                'nama' => 'TPU Tunggul Hitam',
                'lokasi' => 'Koto Tangah, Kota Padang',
                'ringkasan' => 'TPU terbesar dan paling dikenal di Kota Padang dengan akses layanan yang terintegrasi.',
                'highlight' => 'Pusat layanan pemakaman yang aktif, luas, dan mudah dijangkau.',
            ],
            [
                'slug' => 'air-dingin',
                'nama' => 'TPU Air Dingin',
                'lokasi' => 'Koto Tangah, Kota Padang',
                'ringkasan' => 'Melayani kebutuhan pemakaman masyarakat dengan tata ruang yang tertib dan informatif.',
                'highlight' => 'Cocok untuk pengajuan yang membutuhkan alur layanan yang cepat dan terpantau.',
            ],
            [
                'slug' => 'bungus-teluk-kabung',
                'nama' => 'TPU Bungus Teluk Kabung',
                'lokasi' => 'Bungus Teluk Kabung, Kota Padang',
                'ringkasan' => 'Terintegrasi untuk wilayah selatan Kota Padang dengan informasi layanan yang mudah diakses.',
                'highlight' => 'Memberikan alternatif lokasi pemakaman yang terhubung dalam satu sistem.',
            ],
        ],
    ]);
});

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'proses'])->name('login.proses');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'prosesRegister'])->name('register.proses');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'petugas' => redirect()->route('petugas.dashboard'),
            'kepala' => redirect()->route('kepala.dashboard'),
            default => redirect()->route('user.dashboard'),
        };
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
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
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
