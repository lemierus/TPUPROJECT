<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Models\Jenazah;
use App\Models\Makam;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $petugas = auth()->user();

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

        // === PENCARIAN: server-side search untuk tabel "Seluruh Permohonan" ===
        $search = trim((string) $request->query('search', ''));

        $permohonanTerbaru = Permohonan::with(['user', 'jenazah.makam', 'makam'])
            ->where('tpu', $petugas->tpu)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_pemohon', 'like', "%{$search}%")
                        ->orWhere('nama_jenazah', 'like', "%{$search}%")
                        ->orWhere('nik_jenazah', 'like', "%{$search}%")
                        ->orWhere('nama_ahli_waris', 'like', "%{$search}%")
                        ->orWhere('no_hp_ahli_waris', 'like', "%{$search}%")
                        ->orWhere('jenis_permohonan', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('jenazah', function ($jq) use ($search) {
                            $jq->where('nama', 'like', "%{$search}%");
                        })
                        ->orWhereHas('makam', function ($mq) use ($search) {
                            $mq->where('kode_makam', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();
        // === AKHIR PENCARIAN ===

        $pendingRenewalsByJenazah = Permohonan::with(['user', 'jenazah.makam', 'makam'])
            ->where('tpu', $petugas->tpu)
            ->where('jenis_permohonan', Permohonan::JENIS_PERPANJANGAN)
            ->whereIn('status', [
                Permohonan::STATUS_PENDING,
                Permohonan::STATUS_MENUNGGU,
                Permohonan::STATUS_MENUNGGU_KONFIRMASI,
                Permohonan::STATUS_DIPROSES_DARURAT,
                Permohonan::STATUS_ADMINISTRASI_BELUM_LENGKAP,
                Permohonan::STATUS_MENUNGGU_VERIFIKASI_DOKUMEN,
                Permohonan::STATUS_PERLU_PERBAIKAN_DOKUMEN,
            ])
            ->get()
            ->filter(fn (Permohonan $permohonan) => filled($permohonan->jenazah_id))
            ->keyBy('jenazah_id');

        $sourcePermohonans = Permohonan::with(['user', 'jenazah.makam', 'makam'])
            ->where('tpu', $petugas->tpu)
            ->whereIn('jenis_permohonan', [Permohonan::JENIS_MAKAM_BARU, Permohonan::JENIS_DARURAT])
            ->whereIn('status', [Permohonan::STATUS_DISETUJUI, Permohonan::STATUS_SELESAI])
            ->orderByDesc('approved_at')
            ->orderByDesc('updated_at')
            ->get();

        $sourcePermohonansByJenazah = $sourcePermohonans
            ->filter(fn (Permohonan $permohonan) => filled($permohonan->jenazah_id))
            ->keyBy('jenazah_id');

        $perpanjanganPerluDiingatkan = Jenazah::with(['makam.jenazahs', 'permohonan'])
            ->where('tpu', $petugas->tpu)
            ->whereNotNull('tenggat_sewa_makam')
            ->get()
            ->sortByDesc(function (Jenazah $jenazah) {
                return sprintf(
                    '%s-%010d',
                    optional($jenazah->tanggal_wafat)->format('Ymd') ?? '00000000',
                    $jenazah->id
                );
            })
            ->unique(function (Jenazah $jenazah) {
                if ($jenazah->makam_id && $jenazah->isTumpangSari()) {
                    return 'makam:' . $jenazah->makam_id;
                }

                return 'jenazah:' . $jenazah->id;
            })
            ->filter(function (Jenazah $jenazah) {
                return in_array($jenazah->renewalAlertLevel(), ['soon', 'expired'], true);
            })
            ->map(function (Jenazah $jenazah) use ($pendingRenewalsByJenazah, $sourcePermohonans, $sourcePermohonansByJenazah) {
                $pendingRenewal = $pendingRenewalsByJenazah->get($jenazah->id);

                if (! $pendingRenewal && $jenazah->makam_id) {
                    $pendingRenewal = $pendingRenewalsByJenazah->first(function (Permohonan $permohonan) use ($jenazah) {
                        return $permohonan->makam_id === $jenazah->makam_id;
                    });
                }

                $jenazah->pending_renewal_permohonan = $pendingRenewal;
                $sourcePermohonan = $sourcePermohonansByJenazah->get($jenazah->id);

                if (! $sourcePermohonan && $jenazah->makam_id) {
                    $sourcePermohonan = $sourcePermohonans->first(function (Permohonan $permohonan) use ($jenazah) {
                        return $permohonan->makam_id === $jenazah->makam_id;
                    });
                }

                $jenazah->reminder_target_permohonan = $pendingRenewal ?? $sourcePermohonan;

                return $jenazah;
            })
            ->sortBy(function (Jenazah $jenazah) {
                return $jenazah->renewalDueAt()?->timestamp ?? PHP_INT_MAX;
            })
            ->values();

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
            'totalMakam',
            'search'
        ));
    }
}
