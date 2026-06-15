<?php

namespace App\Http\Controllers\Kepala;

use App\Http\Controllers\Controller;
use App\Models\Jenazah;
use App\Models\Makam;
use App\Models\Permohonan;

class DashboardController extends Controller
{
    public function index()
    {
        $tpu = auth()->user()?->tpu;

        return view('kepala.dashboard', [
            'tpu' => $tpu,
            'totalJenazah' => Jenazah::where('tpu', $tpu)->count(),
            'totalMakam' => Makam::where('tpu', $tpu)->count(),
            'totalPermohonan' => Permohonan::where('tpu', $tpu)->count(),
            'permohonanPending' => Permohonan::where('tpu', $tpu)
                ->whereIn('status', ['menunggu', 'pending'])
                ->count(),
            'permohonanDisetujui' => Permohonan::where('tpu', $tpu)
                ->where('status', 'disetujui')
                ->count(),
            'permohonanDitolak' => Permohonan::where('tpu', $tpu)
                ->where('status', 'ditolak')
                ->count(),
        ]);
    }
}
