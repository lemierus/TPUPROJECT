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


/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Tanpa Login)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $landingTpus = Tpu::query()->with('waPetugas')->orderBy('nama')->get()->map(function (Tpu $tpu) {
        $makamTersedia = \App\Models\Makam::where('tpu', $tpu->nama)->where('status', 'kosong')->count();
        $waPetugas = $tpu->waPetugas;

        return [
            'slug' => str()->slug($tpu->nama),
            'nama' => $tpu->nama,
            'lokasi' => $tpu->lokasi ?? '-',
            'ringkasan' => $tpu->ringkasan ?? '-',
            'highlight' => $tpu->highlight ?? '-',
            'makam_tersedia' => $makamTersedia,
            'wa_nama' => $waPetugas?->name,
            'wa_nomor' => $waPetugas?->no_hp,
        ];
    })->values()->all();

    return view('welcome', [
        'landingTpus' => $landingTpus,
    ]);
});

// Autentikasi
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'proses'])->name('login.proses');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'prosesRegister'])->name('register.proses');

// Verifikasi Email
Route::get('/email/verify', [AuthController::class, 'verifyEmailNotice'])
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

/*
|--------------------------------------------------------------------------
| REDIRECT DASHBOARD (Sesuai Role)
|--------------------------------------------------------------------------
*/
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

/*
|--------------------------------------------------------------------------
| PROFILE (Semua Role)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit.page');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Manajemen Users
    Route::resource('users', UserController::class)->except('show');

    // Data Jenazah
    Route::get('/data-jenazah', [DataJenazahController::class, 'index'])->name('data-jenazah');
    Route::get('/data-jenazah/create', [DataJenazahController::class, 'create'])->name('data-jenazah.create');
    Route::post('/data-jenazah', [DataJenazahController::class, 'store'])->name('data-jenazah.store');
    Route::get('/data-jenazah/{id}/edit', [DataJenazahController::class, 'edit'])->name('data-jenazah.edit');
    Route::put('/data-jenazah/{id}', [DataJenazahController::class, 'update'])->name('data-jenazah.update');
    Route::delete('/data-jenazah/{id}', [DataJenazahController::class, 'destroy'])->name('data-jenazah.destroy');

    // Data Makam
    Route::get('/data-makam', [DataMakamController::class, 'index'])->name('data-makam');
    Route::get('/data-makam/create', [DataMakamController::class, 'create'])->name('data-makam.create');
    Route::post('/data-makam', [DataMakamController::class, 'store'])->name('data-makam.store');
    Route::get('/data-makam/{makam}/edit', [DataMakamController::class, 'edit'])->name('data-makam.edit');
    Route::put('/data-makam/{makam}', [DataMakamController::class, 'update'])->name('data-makam.update');
    Route::delete('/data-makam/{makam}', [DataMakamController::class, 'destroy'])->name('data-makam.destroy');

    // Permohonan
    Route::get('/master/permohonan', [PermohonanController::class, 'index'])->name('master.permohonan');
    Route::get('/master/permohonan/create', [PermohonanController::class, 'create'])->name('master.permohonan.create');
    Route::post('/master/permohonan', [PermohonanController::class, 'store'])->name('master.permohonan.store');
    Route::get('/master/permohonan/{permohonan}/edit', [PermohonanController::class, 'edit'])->name('master.permohonan.edit');
    Route::put('/master/permohonan/{permohonan}', [PermohonanController::class, 'update'])->name('master.permohonan.update-data');
    Route::post('/master/permohonan/{id}/status', [PermohonanController::class, 'updateStatus'])->name('master.permohonan.update');
    Route::delete('/master/permohonan/{permohonan}', [PermohonanController::class, 'destroy'])->name('master.permohonan.destroy');

    // Laporan
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

/*
|--------------------------------------------------------------------------
| PETUGAS TPU ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'petugas'])->prefix('petugas')->name('petugas.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');

    // Permohonan
    Route::get('/permohonan', [PetugasPermohonanController::class, 'index'])->name('permohonan');
    Route::get('/permohonan/create', [PetugasPermohonanController::class, 'create'])->name('permohonan.create');
    Route::post('/permohonan', [PetugasPermohonanController::class, 'store'])->name('permohonan.store');
    Route::get('/permohonan/{permohonan}', [PetugasPermohonanController::class, 'show'])->name('permohonan.show');
    Route::get('/permohonan/{permohonan}/edit', [PetugasPermohonanController::class, 'edit'])->name('permohonan.edit');
    Route::put('/permohonan/{permohonan}', [PetugasPermohonanController::class, 'update'])->name('permohonan.update');
    Route::post('/permohonan/{permohonan}/proses-darurat', [PetugasPermohonanController::class, 'prosesDarurat'])->name('permohonan.proses-darurat');
    Route::post('/permohonan/{permohonan}/selesaikan-pemakaman', [PetugasPermohonanController::class, 'selesaikanPemakaman'])->name('permohonan.selesaikan-pemakaman');
    Route::post('/permohonan/{permohonan}/verifikasi-dokumen', [PetugasPermohonanController::class, 'verifikasiDokumen'])->name('permohonan.verifikasi-dokumen');
    Route::post('/permohonan/{permohonan}/approve', [PetugasPermohonanController::class, 'approve'])->name('permohonan.approve');
    Route::post('/permohonan/{permohonan}/reject', [PetugasPermohonanController::class, 'reject'])->name('permohonan.reject');

    // Data Jenazah
    Route::get('/data-jenazah', [DataJenazahController::class, 'index'])->name('data-jenazah');
    Route::get('/data-jenazah/create', [DataJenazahController::class, 'create'])->name('data-jenazah.create');
    Route::post('/data-jenazah', [DataJenazahController::class, 'store'])->name('data-jenazah.store');
    Route::get('/data-jenazah/{id}/edit', [DataJenazahController::class, 'edit'])->name('data-jenazah.edit');
    Route::put('/data-jenazah/{id}', [DataJenazahController::class, 'update'])->name('data-jenazah.update');
    Route::delete('/data-jenazah/{id}', [DataJenazahController::class, 'destroy'])->name('data-jenazah.destroy');

    // Data Makam
    Route::get('/data-makam', [DataMakamController::class, 'index'])->name('data-makam');
    Route::get('/data-makam/create', [DataMakamController::class, 'create'])->name('data-makam.create');
    Route::post('/data-makam', [DataMakamController::class, 'store'])->name('data-makam.store');
    Route::get('/data-makam/{makam}/edit', [DataMakamController::class, 'edit'])->name('data-makam.edit');
    Route::put('/data-makam/{makam}', [DataMakamController::class, 'update'])->name('data-makam.update');
    Route::delete('/data-makam/{makam}', [DataMakamController::class, 'destroy'])->name('data-makam.destroy');

    // Data Makam
    Route::get('/master/permohonan', [PermohonanController::class, 'index'])->name('master.permohonan');
    Route::get('/master/permohonan/create', [PermohonanController::class, 'create'])->name('master.permohonan.create');
    Route::post('/master/permohonan', [PermohonanController::class, 'store'])->name('master.permohonan.store');
    Route::get('/master/permohonan/{permohonan}/edit', [PermohonanController::class, 'edit'])->name('master.permohonan.edit');
    Route::put('/master/permohonan/{permohonan}', [PermohonanController::class, 'update'])->name('master.permohonan.update-data');
    Route::post('/master/permohonan/{id}/status', [PermohonanController::class, 'updateStatus'])->name('master.permohonan.update');
    Route::delete('/master/permohonan/{permohonan}', [PermohonanController::class, 'destroy'])->name('master.permohonan.destroy');

    // Laporan
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

/*
|--------------------------------------------------------------------------
| KEPALA TPU ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'kepala'])->prefix('kepala')->name('kepala.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [KepalaDashboardController::class, 'index'])->name('dashboard');

    // Manajemen Users
    Route::resource('users', UserController::class)->except('show');

    // Laporan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
    Route::get('/laporan/word', [LaporanController::class, 'word'])->name('laporan.word');
    Route::get('/laporan/print', [LaporanController::class, 'print'])->name('laporan.print');
    Route::get('/laporan/excel', [LaporanController::class, 'excel'])->name('laporan.excel');

    // Data Jenazah (View Only)
    Route::get('/data-jenazah', [DataJenazahController::class, 'index'])->name('data-jenazah');

    // Data Makam
    Route::get('/data-makam', [DataMakamController::class, 'index'])->name('data-makam');
    Route::get('/data-makam/create', [DataMakamController::class, 'create'])->name('data-makam.create');
    Route::post('/data-makam', [DataMakamController::class, 'store'])->name('data-makam.store');
    Route::get('/data-makam/{makam}/edit', [DataMakamController::class, 'edit'])->name('data-makam.edit');
    Route::put('/data-makam/{makam}', [DataMakamController::class, 'update'])->name('data-makam.update');
    Route::delete('/data-makam/{makam}', [DataMakamController::class, 'destroy'])->name('data-makam.destroy');
});

/*
|--------------------------------------------------------------------------
| KDLH (KEPALA DINAS LINGKUNGAN HIDUP) ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'kdlh'])->prefix('kdlh')->name('kdlh.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [KdlhDashboardController::class, 'index'])->name('dashboard');

    // Manajemen Users
    Route::resource('users', UserController::class)->except('show');

    // Manajemen TPU
    Route::resource('tpu', KdlhTpuController::class)->except(['show']);

    // Data Jenazah (View Only)
    Route::get('/data-jenazah', [DataJenazahController::class, 'index'])->name('data-jenazah');

    // Data Makam (View Only)
    Route::get('/data-makam', [DataMakamController::class, 'index'])->name('data-makam');

    // Laporan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
    Route::get('/laporan/word', [LaporanController::class, 'word'])->name('laporan.word');
});

/*
|--------------------------------------------------------------------------
| USER / AHLI WARIS ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'user', 'verified'])->prefix('user')->name('user.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // Permohonan
    Route::get('/permohonan/create', [UserPermohonanController::class, 'create'])->name('permohonan.create');
    Route::post('/permohonan', [UserPermohonanController::class, 'store'])->name('permohonan.store');
    Route::get('/permohonan/{permohonan}/darurat-sukses', [UserPermohonanController::class, 'daruratSukses'])->name('permohonan.darurat-sukses');
    Route::get('/permohonan/{permohonan}/ringkasan', [UserPermohonanController::class, 'summary'])->name('permohonan.summary');
    Route::get('/permohonan/{permohonan}/lengkapi-dokumen', [UserPermohonanController::class, 'lengkapiDokumen'])->name('permohonan.lengkapi-dokumen');
    Route::put('/permohonan/{permohonan}/lengkapi-dokumen', [UserPermohonanController::class, 'updateDokumen'])->name('permohonan.update-dokumen');
    Route::get('/permohonan/{permohonan}/edit', [UserPermohonanController::class, 'edit'])->name('permohonan.edit');
    Route::put('/permohonan/{permohonan}', [UserPermohonanController::class, 'update'])->name('permohonan.update');
});

require __DIR__ . '/settings.php';