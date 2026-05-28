<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Models\Jenazah;
use App\Models\Makam;

class DashboardController extends Controller
{
    public function index()
    {
        $petugas = auth()->user();

        // Data permohonan yang ditangani petugas ini (berdasarkan TPU)
        $permohonanDisetujui = Permohonan::where('tpu', $petugas->tpu)
            ->where('status', 'disetujui')
            ->count();

        $permohonanDitolak = Permohonan::where('tpu', $petugas->tpu)
            ->where('status', 'ditolak')
            ->count();

        $totalPermohonan = Permohonan::where('tpu', $petugas->tpu)->count();

        $permohonanMenunggu = Permohonan::where('tpu', $petugas->tpu)
            ->whereIn('status', ['pending', 'menunggu'])
            ->count();

        // Permohonan terbaru untuk ditampilkan di tabel
        $permohonanTerbaru = Permohonan::with(['user'])
            ->where('tpu', $petugas->tpu)
            ->latest('created_at')
            ->take(10)
            ->get();

        // Total jenazah dan makam untuk informasi sistem (filtered by TPU)
        $totalJenazah = Jenazah::where('tpu', $petugas->tpu)->count();
        $totalMakam = Makam::where('tpu', $petugas->tpu)->count();

        return view('petugas.dashboard', compact(
            'petugas',
            'permohonanDisetujui',
            'permohonanDitolak',
            'totalPermohonan',
            'permohonanMenunggu',
            'permohonanTerbaru',
            'totalJenazah',
            'totalMakam'
        ));
    }
}
