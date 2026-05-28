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
                    'ringkasan' => 'Informasi lokasi, layanan, dan gambaran area TPU Tunggul Hitam.',
                ],
                [
                    'slug' => 'bungus-teluk-kabung',
                    'nama' => 'TPU Bungus Teluk Kabung',
                    'lokasi' => 'Bungus Teluk Kabung, Kota Padang',
                    'ringkasan' => 'Informasi TPU Bungus Teluk Kabung untuk kebutuhan pelayanan pemakaman masyarakat.',
                ],
                [
                    'slug' => 'air-dingin',
                    'nama' => 'TPU Air Dingin',
                    'lokasi' => 'Koto Tangah, Kota Padang',
                    'ringkasan' => 'Informasi TPU Air Dingin beserta layanan dasar yang tersedia di lokasi.',
                ],
        ];

        $permohonanSaya = Permohonan::with(['jenazah', 'makam'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $totalPermohonan = Permohonan::where('user_id', auth()->id())->count();
        $permohonanMenunggu = Permohonan::where('user_id', auth()->id())->where('status', 'menunggu')->count();
        $permohonanDisetujui = Permohonan::where('user_id', auth()->id())->where('status', 'disetujui')->count();

        return view('user.dashboard', compact(
            'daftarTpu',
            'permohonanSaya',
            'totalPermohonan',
            'permohonanMenunggu',
            'permohonanDisetujui'
        ));
    }
}
