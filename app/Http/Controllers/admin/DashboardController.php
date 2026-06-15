<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Makam;
use App\Models\Permohonan;
use App\Models\Jenazah;

class DashboardController extends Controller
{
    public function index()
    {
        $totalJenazah = Jenazah::count();
        $totalMakam = Makam::count();
        $totalPermohonan = Permohonan::count();

        $totalPetugas = User::where('role', User::ROLE_PETUGAS)->count();
        $totalUser = User::where('role', User::ROLE_USER)->count();

        $permohonanDisetujui = Permohonan::where('status', 'disetujui')->count();
        $permohonanDitolak = Permohonan::where('status', 'ditolak')->count();
        $permohonanPending = Permohonan::whereIn('status', ['menunggu', 'pending'])->count();
        $permohonanTerbaru = Permohonan::with(['user', 'jenazah'])
            ->latest('created_at')
            ->take(5)
            ->get();

        $perTpuStats = collect(User::tpuOptions())->map(function (string $tpu) {
            return [
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
            ];
        });

        // Chart data (contoh: jumlah jenazah per bulan)
        $chartLabels = [];
        $chartData = [];

        for ($i = 1; $i <= 12; $i++) {
            $chartLabels[] = Carbon::create()->month($i)->translatedFormat('M');

            $chartData[] = Jenazah::whereMonth('created_at', $i)
                ->whereYear('created_at', date('Y'))
                ->count();
        }

        return view('admin.pages.dashboard.index', compact(
            'totalJenazah',
            'totalMakam',
            'totalPermohonan',
            'totalPetugas',
            'totalUser',
            'permohonanDisetujui',
            'permohonanDitolak',
            'permohonanPending',
            'permohonanTerbaru',
            'perTpuStats',
            'chartLabels',
            'chartData'
        ));
    }
}
