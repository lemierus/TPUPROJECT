<?php

namespace App\Http\Controllers\Kdlh;

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
        $tpus = Tpu::query()->orderBy('nama')->get();

        $summaries = $tpus->map(function (Tpu $tpu) {
            return [
                'id' => $tpu->id,
                'nama' => $tpu->nama,
                'lokasi' => $tpu->lokasi ?? '-',
                'ringkasan' => $tpu->ringkasan ?? '-',
                'highlight' => $tpu->highlight ?? '-',
                'deskripsi' => $tpu->deskripsi ?? '-',
                'jenazah' => Jenazah::where('tpu', $tpu->nama)->count(),
                'makam' => Makam::where('tpu', $tpu->nama)->count(),
                'permohonan' => Permohonan::where('tpu', $tpu->nama)->count(),
            ];
        });

        return view('kdlh.dashboard', [
            'totalTpu' => $tpus->count(),
            'totalKepalaTpu' => User::where('role', User::ROLE_KEPALA)->count(),
            'totalJenazah' => Jenazah::count(),
            'totalMakam' => Makam::count(),
            'totalPermohonan' => Permohonan::count(),
            'tpuSummaries' => $summaries,
        ]);
    }
}
