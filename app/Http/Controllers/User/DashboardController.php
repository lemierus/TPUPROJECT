<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Makam;
use App\Models\Permohonan;
use App\Models\Tpu;

class DashboardController extends Controller
{
    public function index()
    {
        $daftarTpu = Tpu::query()
            ->with('waPetugas')
            ->orderBy('nama')
            ->get()
            ->map(function (Tpu $tpu) {
                $makamTersedia = Makam::where('tpu', $tpu->nama)->where('status', 'kosong')->count();
                $waPetugas = $tpu->waPetugas;

                return [
                    'slug' => str()->slug($tpu->nama),
                    'nama' => $tpu->nama,
                    'lokasi' => $tpu->lokasi ?? '-',
                    'ringkasan' => $tpu->ringkasan ?? '-',
                    'highlight' => $tpu->highlight ?? '-',
                    'makam_tersedia' => $makamTersedia,
                    'wa_nama' => $waPetugas?->name,
                    'wa_nomor' => $waPetugas?->no_hp,
                ];
            })
            ->values()
            ->all();

        $permohonanSemua = Permohonan::with(['jenazah.makam', 'makam'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $permohonanLengkapiDokumen = $permohonanSemua
            ->filter(fn (Permohonan $permohonan) => $permohonan->needsDocumentCompletion())
            ->values();

        $permohonanSaya = $permohonanSemua
            ->filter(function (Permohonan $permohonan) {
                return ! ($permohonan->jenis_permohonan === 'perpanjangan' && $permohonan->status === 'disetujui');
            })
            ->values();

        $pengingatSewaMakam = $permohonanSemua
            ->filter(function (Permohonan $permohonan) {
                if ($permohonan->jenis_permohonan !== 'makam_baru') {
                    return false;
                }

                // Tampilkan pengingat untuk semua item yang memiliki tenggat mendekati/terlewat
                if (! in_array($permohonan->renewalAlertLevel(), ['soon', 'expired'], true)) {
                    return false;
                }

                if ($permohonan->status !== 'disetujui' || ! $permohonan->jenazah_id) {
                    return false;
                }
                
                return true;
            })
            ->filter(function (Permohonan $permohonan) use ($permohonanSemua) {
                return ! $permohonanSemua->contains(function (Permohonan $other) use ($permohonan) {
                    return $other->id !== $permohonan->id
                        && $other->jenis_permohonan === 'perpanjangan'
                        && $other->jenazah_id === $permohonan->jenazah_id
                        && in_array($other->status, ['pending', 'menunggu'], true);
                });
            })
            ->sortBy(function (Permohonan $permohonan) {
                return $permohonan->renewalDueAt()?->timestamp ?? PHP_INT_MAX;
            })
            ->values();

        $totalPermohonan = $permohonanSemua->count();
        $permohonanMenunggu = $permohonanSemua->filter(function (Permohonan $permohonan) {
            return in_array($permohonan->status, [
                Permohonan::STATUS_MENUNGGU,
                Permohonan::STATUS_PENDING,
                Permohonan::STATUS_MENUNGGU_KONFIRMASI,
                Permohonan::STATUS_DIPROSES_DARURAT,
                Permohonan::STATUS_ADMINISTRASI_BELUM_LENGKAP,
                Permohonan::STATUS_MENUNGGU_VERIFIKASI_DOKUMEN,
                Permohonan::STATUS_PERLU_PERBAIKAN_DOKUMEN,
            ], true);
        })->count();
        $permohonanDisetujui = $permohonanSemua->filter(function (Permohonan $permohonan) {
            return in_array($permohonan->status, [Permohonan::STATUS_DISETUJUI, Permohonan::STATUS_SELESAI], true);
        })->count();
        $totalTpu = count($daftarTpu);

        return view('user.dashboard', compact(
            'daftarTpu',
            'permohonanSaya',
            'permohonanLengkapiDokumen',
            'pengingatSewaMakam',
            'totalPermohonan',
            'permohonanMenunggu',
            'permohonanDisetujui',
            'totalTpu'
        ));
    }
}
