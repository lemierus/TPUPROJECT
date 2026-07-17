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
            ->whereIn('status', [
                Permohonan::STATUS_PENDING,
                Permohonan::STATUS_MENUNGGU,
                Permohonan::STATUS_MENUNGGU_KONFIRMASI,
                Permohonan::STATUS_DIPROSES_DARURAT,
                Permohonan::STATUS_MENUNGGU_VERIFIKASI_DOKUMEN,
                Permohonan::STATUS_PERLU_PERBAIKAN_DOKUMEN,
            ])
            ->count();

        // === PERUBAHAN: seluruh permohonan (bukan cuma 10 terbaru), dengan
        // pagination 10 data per halaman menggunakan paginate() bawaan Laravel.
        $permohonanTerbaru = Permohonan::with(['user', 'jenazah.makam', 'makam'])
            ->where('tpu', $petugas->tpu)
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();
        // === AKHIR PERUBAHAN ===

        $perpanjanganPerluDiingatkan = Permohonan::with(['user', 'jenazah.makam', 'makam'])
            ->where('tpu', $petugas->tpu)
            ->where('status', 'disetujui')
            ->where('jenis_permohonan', 'makam_baru')
            ->get()
            ->filter(function (Permohonan $permohonan) {
                $level = $permohonan->renewalAlertLevel();

                return in_array($level, ['soon', 'expired'], true);
            })
            ->sortBy(function (Permohonan $permohonan) {
                return $permohonan->renewalDueAt()?->timestamp ?? PHP_INT_MAX;
            })
            ->values();

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
            'perpanjanganPerluDiingatkan',
            'totalJenazah',
            'totalMakam'
        ));
    }
}