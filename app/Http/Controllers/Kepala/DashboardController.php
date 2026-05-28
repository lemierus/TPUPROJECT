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
        return view('kepala.dashboard', [
            'totalJenazah' => Jenazah::count(),
            'totalMakam' => Makam::count(),
            'totalPermohonan' => Permohonan::count(),
            'permohonanPending' => Permohonan::where('status', 'menunggu')->count(),
        ]);
    }
}
