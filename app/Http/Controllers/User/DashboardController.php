<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Makam;
use App\Models\Permohonan;
use App\Models\Tpu;
use App\Models\Jenazah;

class DashboardController extends Controller
{
    public function index()
    {
        $permohonanSaya = Permohonan::with(['jenazah.makam', 'makam'])
            ->where('user_id', auth()->id())
            ->where(function ($query) {
                $query->where('jenis_permohonan', '!=', Permohonan::JENIS_PERPANJANGAN)
                    ->orWhere('status', '!=', Permohonan::STATUS_DISETUJUI);
            })
            ->latest()
            ->paginate(5, ['*'], 'permohonan_page')
            ->withQueryString();

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

        $pendingRenewals = $permohonanSemua
            ->filter(function (Permohonan $permohonan) {
                return $permohonan->jenis_permohonan === Permohonan::JENIS_PERPANJANGAN
                    && in_array($permohonan->status, [
                        Permohonan::STATUS_PENDING,
                        Permohonan::STATUS_MENUNGGU,
                        Permohonan::STATUS_MENUNGGU_KONFIRMASI,
                        Permohonan::STATUS_DIPROSES_DARURAT,
                        Permohonan::STATUS_ADMINISTRASI_BELUM_LENGKAP,
                        Permohonan::STATUS_MENUNGGU_VERIFIKASI_DOKUMEN,
                        Permohonan::STATUS_PERLU_PERBAIKAN_DOKUMEN,
                    ], true);
            })
            ->values();

        $sourcePermohonans = $permohonanSemua
            ->filter(function (Permohonan $permohonan) {
                return in_array($permohonan->jenis_permohonan, [
                        Permohonan::JENIS_MAKAM_BARU,
                        Permohonan::JENIS_DARURAT,
                    ], true)
                    && in_array($permohonan->status, [
                        Permohonan::STATUS_DISETUJUI,
                        Permohonan::STATUS_SELESAI,
                    ], true)
                    && filled($permohonan->jenazah_id)
                    && $permohonan->jenazah;
            })
            ->sortByDesc(function (Permohonan $permohonan) {
                $jenazah = $permohonan->jenazah;

                return sprintf(
                    '%s-%010d',
                    optional($jenazah?->tanggal_wafat)->format('Ymd') ?? '00000000',
                    $jenazah?->id ?? 0
                );
            })
            ->unique(function (Permohonan $permohonan) {
                $jenazah = $permohonan->jenazah;

                if ($jenazah?->makam_id && $jenazah->isTumpangSari()) {
                    return 'makam:' . $jenazah->makam_id;
                }

                return 'jenazah:' . $permohonan->jenazah_id;
            })
            ->filter(function (Permohonan $permohonan) {
                return in_array($permohonan->jenazah?->renewalAlertLevel(), ['soon', 'expired'], true);
            })
            ->map(function (Permohonan $permohonan) use ($pendingRenewals) {
                $jenazah = $permohonan->jenazah;

                $pendingRenewal = $pendingRenewals->first(function (Permohonan $other) use ($permohonan, $jenazah) {
                    if ($other->jenazah_id === $permohonan->jenazah_id) {
                        return true;
                    }

                    return $jenazah?->makam_id
                        && $other->makam_id === $jenazah->makam_id;
                });

                $permohonan->pending_renewal_permohonan = $pendingRenewal;

                return $permohonan;
            })
            ->sortBy(function (Permohonan $permohonan) {
                return $permohonan->jenazah?->renewalDueAt()?->timestamp
                    ?? $permohonan->renewalDueAt()?->timestamp
                    ?? PHP_INT_MAX;
            })
            ->values();

        $pengingatSewaMakam = $sourcePermohonans;

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
