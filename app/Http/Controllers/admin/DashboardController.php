<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Makam;
use App\Models\Jenazah;
use App\Models\Permohonan;

class DashboardController extends Controller
{
    public function index()
    {
        $totalJenazah = Jenazah::count();
        $totalMakam = Makam::count();
        $totalPermohonan = Permohonan::count();

        $permohonanPending = Permohonan::where('status', 'pending')->count();
        $permohonanDisetujui = Permohonan::where('status', 'disetujui')->count();
        $permohonanDitolak = Permohonan::where('status', 'ditolak')->count();

        $totalPetugas = User::where('role', 'petugas')->count();
        $totalUser = User::where('role', 'masyarakat')->count();

        $permohonanTerbaru = Permohonan::latest()->take(5)->get();

        $chartLabels = [];
        $chartData = [];

        for ($i = 1; $i <= 12; $i++) {
            $chartLabels[] = Carbon::create()->month($i)->translatedFormat('M');

            $chartData[] = Permohonan::whereMonth('created_at', $i)
                ->whereYear('created_at', date('Y'))
                ->count();
        }

        return view('admin.pages.dashboard.index', compact(
            'totalJenazah',
            'totalMakam',
            'totalPermohonan',
            'permohonanPending',
            'permohonanDisetujui',
            'permohonanDitolak',
            'totalPetugas',
            'totalUser',
            'permohonanTerbaru',
            'chartLabels',
            'chartData'
        ));
    }
}
