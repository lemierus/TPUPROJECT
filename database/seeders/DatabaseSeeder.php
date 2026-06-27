<?php

namespace Database\Seeders;

use App\Models\Tpu;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@tpu.test'],
            [
                'name' => 'Admin TPU',
                'nip' => '197501012005011001',
                'no_hp' => '6281234567888',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        User::updateOrCreate(
            ['email' => 'kdlh@tpu.test'],
            [
                'name' => 'Kepala Dinas Lingkungan Hidup',
                'nip' => '198001012010011001',
                'no_hp' => '6281234567890',
                'password' => Hash::make('password'),
                'role' => User::ROLE_KDLH,
            ]
        );

        // Satu akun kepala TPU pusat
        $kepalaPusat = User::updateOrCreate(
            ['email' => 'kepala@tpu.test'],
            [
                'name' => 'Kepala TPU',
                'nip' => '198505052015011001',
                'no_hp' => '6281234567891',
                'password' => Hash::make('password'),
                'role' => User::ROLE_KEPALA,
            ]
        );
        if (Schema::hasColumn('users', 'tpu')) {
            $kepalaPusat->update(['tpu' => null]);
        }

        User::where('role', User::ROLE_KEPALA)
            ->where('email', '!=', 'kepala@tpu.test')
            ->delete();

        // Petugas untuk TPU Tunggul Hitam
        $petugas1 = User::updateOrCreate(
            ['email' => 'petugas1@tpu.test'],
            [
                'name' => 'Petugas Tunggul Hitam',
                'nip' => '199001012020011001',
                'no_hp' => '6281234567892',
                'password' => Hash::make('password'),
                'role' => User::ROLE_PETUGAS,
            ]
        );
        if (Schema::hasColumn('users', 'tpu')) {
            $petugas1->update(['tpu' => 'TPU Tunggul Hitam']);
        }

        // Petugas untuk TPU Bungus Teluk Kabung
        $petugas2 = User::updateOrCreate(
            ['email' => 'petugas2@tpu.test'],
            [
                'name' => 'Petugas Bungus Teluk Kabung',
                'nip' => '199002022020011002',
                'no_hp' => '6281234567893',
                'password' => Hash::make('password'),
                'role' => User::ROLE_PETUGAS,
            ]
        );
        if (Schema::hasColumn('users', 'tpu')) {
            $petugas2->update(['tpu' => 'TPU Bungus Teluk Kabung']);
        }

        // Petugas untuk TPU Air Dingin
        $petugas3 = User::updateOrCreate(
            ['email' => 'petugas3@tpu.test'],
            [
                'name' => 'Petugas Air Dingin',
                'nip' => '199003032020011003',
                'no_hp' => '6281234567894',
                'password' => Hash::make('password'),
                'role' => User::ROLE_PETUGAS,
            ]
        );
        if (Schema::hasColumn('users', 'tpu')) {
            $petugas3->update(['tpu' => 'TPU Air Dingin']);
        }

        User::updateOrCreate(
            ['email' => 'user@tpu.test'],
            [
                'name' => 'Ahli Waris',
                'password' => Hash::make('password'),
                'role' => User::ROLE_USER,
            ]
        );

        foreach ([
            [
                'nama' => 'TPU Tunggul Hitam',
                'lokasi' => 'Koto Tangah, Kota Padang',
                'ringkasan' => 'TPU terbesar dan paling dikenal di Kota Padang dengan akses layanan yang terintegrasi.',
                'highlight' => 'Pusat layanan pemakaman yang aktif, luas, dan mudah dijangkau.',
                'deskripsi' => 'TPU unggulan yang menjadi pusat layanan pemakaman untuk wilayah Tunggul Hitam.',
                'wa_petugas_id' => $petugas1->id,
            ],
            [
                'nama' => 'TPU Air Dingin',
                'lokasi' => 'Koto Tangah, Kota Padang',
                'ringkasan' => 'Melayani kebutuhan pemakaman masyarakat dengan tata ruang yang tertib dan informatif.',
                'highlight' => 'Cocok untuk pengajuan yang membutuhkan alur layanan yang cepat dan terpantau.',
                'deskripsi' => 'TPU dengan pengelolaan administrasi yang tertib dan terintegrasi penuh.',
                'wa_petugas_id' => $petugas3->id,
            ],
            [
                'nama' => 'TPU Bungus Teluk Kabung',
                'lokasi' => 'Bungus Teluk Kabung, Kota Padang',
                'ringkasan' => 'Terintegrasi untuk wilayah selatan Kota Padang dengan informasi layanan yang mudah diakses.',
                'highlight' => 'Memberikan alternatif lokasi pemakaman yang terhubung dalam satu sistem.',
                'deskripsi' => 'TPU yang melayani wilayah selatan kota dengan koordinasi terpadu.',
                'wa_petugas_id' => $petugas2->id,
            ],
        ] as $tpuData) {
            Tpu::updateOrCreate(['nama' => $tpuData['nama']], $tpuData);
        }
    }
}
