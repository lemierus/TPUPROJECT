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
