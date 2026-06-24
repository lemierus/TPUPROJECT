<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Models\Tpu;

class DashboardController extends Controller
{
    public function index()
    {
        $daftarTpu = Tpu::query()
            ->orderBy('nama')
            ->get()
            ->map(function (Tpu $tpu) {
                return [
                    'slug' => str()->slug($tpu->nama),
                    'nama' => $tpu->nama,
                    'lokasi' => $tpu->lokasi ?? '-',
                    'ringkasan' => $tpu->ringkasan ?? '-',
                    'highlight' => $tpu->highlight ?? '-',
                ];
            })
            ->values()
            ->all();

        $permohonanSemua = Permohonan::with(['jenazah.makam', 'makam'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $permohonanSaya = $permohonanSemua
            ->filter(function (Permohonan $permohonan) {
                return ! ($permohonan->jenis_permohonan === 'perpanjangan' && $permohonan->status === 'disetujui');
            })
            ->values();

        $pengingatSewaMakam = $permohonanSemua
            ->filter(function (Permohonan $permohonan) {
                // Tampilkan pengingat untuk semua item yang memiliki tenggat mendekati/terlewat
                if (! in_array($permohonan->renewalAlertLevel(), ['soon', 'expired'], true)) {
                    return false;
                }

                // Untuk makam_baru: hanya tampilkan jika sudah disetujui dan memiliki jenazah_id
                if ($permohonan->jenis_permohonan === 'makam_baru') {
                    if ($permohonan->status !== 'disetujui' || ! $permohonan->jenazah_id) {
                        return false;
                    }
                }

                return true;
            })
            ->filter(function (Permohonan $permohonan) use ($permohonanSemua) {
                // Sembunyikan makam_baru jika sudah ada pengajuan perpanjangan untuk jenazah yang sama
                if ($permohonan->jenis_permohonan !== 'makam_baru') {
                    return true;
                }

                if (! $permohonan->jenazah_id) {
                    return true;
                }

                return ! $permohonanSemua->contains(function (Permohonan $other) use ($permohonan) {
                    return $other->id !== $permohonan->id
                        && $other->jenis_permohonan === 'perpanjangan'
                        && $other->jenazah_id === $permohonan->jenazah_id;
                });
            })
            ->sortBy(function (Permohonan $permohonan) {
                return $permohonan->renewalDueAt()?->timestamp ?? PHP_INT_MAX;
            })
            ->values();

        $totalPermohonan = $permohonanSemua->count();
        $permohonanMenunggu = $permohonanSemua->where('status', 'menunggu')->count();
        $permohonanDisetujui = $permohonanSemua->where('status', 'disetujui')->count();
        $totalTpu = count($daftarTpu);

        return view('user.dashboard', compact(
            'daftarTpu',
            'permohonanSaya',
            'pengingatSewaMakam',
            'totalPermohonan',
            'permohonanMenunggu',
            'permohonanDisetujui',
            'totalTpu'
        ));
    }
}
