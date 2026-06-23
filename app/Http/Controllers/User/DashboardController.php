<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;

class DashboardController extends Controller
{
    public function index()
    {
        $daftarTpu = [
                [
                    'slug' => 'tunggul-hitam',
                    'nama' => 'TPU Tunggul Hitam',
                    'lokasi' => 'Koto Tangah, Kota Padang',
                    'ringkasan' => 'Untuk menghubungi petugas Tempat Pemakaman Umum (TPU) di bawah naungan UPT TPU Dinas Lingkungan Hidup, Anda dapat menghubungi nomor WhatsApp/Telepon resmi berikut: 0813 6302 0913',
                ],
                [
                    'slug' => 'bungus-teluk-kabung',
                    'nama' => 'TPU Bungus Teluk Kabung',
                    'lokasi' => 'Bungus Teluk Kabung, Kota Padang',
                    'ringkasan' => 'Untuk menghubungi petugas Tempat Pemakaman Umum (TPU) di bawah naungan UPT TPU Dinas Lingkungan Hidup, Anda dapat menghubungi nomor WhatsApp/Telepon resmi berikut: 0813 6302 0913',
                ],
                [
                    'slug' => 'air-dingin',
                    'nama' => 'TPU Air Dingin',
                    'lokasi' => 'Koto Tangah, Kota Padang',
                    'ringkasan' => 'Untuk menghubungi petugas Tempat Pemakaman Umum (TPU) di bawah naungan UPT TPU Dinas Lingkungan Hidup, Anda dapat menghubungi nomor WhatsApp/Telepon resmi berikut: 0813 6302 0913',
                ],
        ];

        $permohonanSaya = Permohonan::with(['jenazah.makam', 'makam'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get()
            ->filter(function (Permohonan $permohonan) {
                return ! ($permohonan->jenis_permohonan === 'perpanjangan' && $permohonan->status === 'disetujui');
            })
            ->values();

        $pengingatSewaMakam = $permohonanSaya
            ->filter(function (Permohonan $permohonan) {
                return in_array($permohonan->renewalAlertLevel(), ['soon', 'expired'], true);
            })
            ->sortBy(function (Permohonan $permohonan) {
                return $permohonan->renewalDueAt()?->timestamp ?? PHP_INT_MAX;
            })
            ->values();

        $totalPermohonan = $permohonanSaya->count();
        $permohonanMenunggu = $permohonanSaya->where('status', 'menunggu')->count();
        $permohonanDisetujui = $permohonanSaya->where('status', 'disetujui')->count();

        return view('user.dashboard', compact(
            'daftarTpu',
            'permohonanSaya',
            'pengingatSewaMakam',
            'totalPermohonan',
            'permohonanMenunggu',
            'permohonanDisetujui'
        ));
    }
}
