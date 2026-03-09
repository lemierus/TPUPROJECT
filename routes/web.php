<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Master\DataJenazahController;
use App\Http\Controllers\Master\DataMakamController;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/data-jenazah', [DataJenazahController::class, 'index'])->name('data-jenazah');
Route::get('/data-makam', [DataMakamController::class, 'index'])->name('data-makam');

require __DIR__.'/settings.php';
