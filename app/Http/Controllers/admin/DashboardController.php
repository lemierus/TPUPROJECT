<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Makam;
use App\Models\Jenazah;

class DashboardController extends Controller
{
    public function index()
    {
        $totalJenazah = Jenazah::count();
        $totalMakam = Makam::count();

        $totalPetugas = User::where('role', User::ROLE_PETUGAS)->count();
        $totalUser = User::where('role', User::ROLE_USER)->count();

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
            'totalPetugas',
            'totalUser',
            'chartLabels',
            'chartData'
        ));
    }
}
