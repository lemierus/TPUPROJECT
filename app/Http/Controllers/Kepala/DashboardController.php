<?php

namespace App\Http\Controllers\Kepala;

use App\Http\Controllers\Controller;
use App\Models\Jenazah;
use App\Models\Makam;
use App\Models\Permohonan;
use App\Models\Tpu;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $tpuSummaries = Tpu::query()
            ->orderBy('nama')
            ->get()
            ->map(function (Tpu $tpu) {
            return [
                'id' => $tpu->id,
                'name' => $tpu->nama,
                'description' => $tpu->deskripsi ?: ($tpu->ringkasan ?: '-'),
                'jenazah' => Jenazah::where('tpu', $tpu->nama)->count(),
                'makam' => Makam::where('tpu', $tpu->nama)->count(),
                'permohonan' => Permohonan::where('tpu', $tpu->nama)->count(),
                'pending' => Permohonan::where('tpu', $tpu->nama)
                    ->whereIn('status', ['menunggu', 'pending'])
                    ->count(),
                'approved' => Permohonan::where('tpu', $tpu->nama)
                    ->where('status', 'disetujui')
                    ->count(),
                'rejected' => Permohonan::where('tpu', $tpu->nama)
                    ->where('status', 'ditolak')
                    ->count(),
            ];
        });

        return view('kepala.dashboard', [
            'totalJenazah' => Jenazah::count(),
            'totalMakam' => Makam::count(),
            'totalPermohonan' => Permohonan::count(),
            'permohonanPending' => Permohonan::whereIn('status', ['menunggu', 'pending'])
                ->count(),
            'permohonanDisetujui' => Permohonan::where('status', 'disetujui')
                ->count(),
            'permohonanDitolak' => Permohonan::where('status', 'ditolak')
                ->count(),
            'activeTpuLabel' => 'Semua TPU',
            'totalTpu' => $tpuSummaries->count(),
            'tpuSummaries' => $tpuSummaries,
        ]);
    }
}
