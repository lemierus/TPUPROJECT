<?php

namespace Database\Seeders;

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
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        // Kepala TPU Tunggul Hitam
        $kepalaTunggulHitam = User::updateOrCreate(
            ['email' => 'kepala@tpu.test'],
            [
                'name' => 'Kepala TPU Tunggul Hitam',
                'password' => Hash::make('password'),
                'role' => User::ROLE_KEPALA,
            ]
        );
        if (Schema::hasColumn('users', 'tpu')) {
            $kepalaTunggulHitam->update(['tpu' => 'TPU Tunggul Hitam']);
        }

        // Kepala TPU Air Dingin
        $kepalaAirDingin = User::updateOrCreate(
            ['email' => 'kepala.airdingin@tpu.test'],
            [
                'name' => 'Kepala TPU Air Dingin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_KEPALA,
            ]
        );
        if (Schema::hasColumn('users', 'tpu')) {
            $kepalaAirDingin->update(['tpu' => 'TPU Air Dingin']);
        }

        // Kepala TPU Bungus Teluk Kabung
        $kepalaBungus = User::updateOrCreate(
            ['email' => 'kepala.bungus@tpu.test'],
            [
                'name' => 'Kepala TPU Bungus Teluk Kabung',
                'password' => Hash::make('password'),
                'role' => User::ROLE_KEPALA,
            ]
        );
        if (Schema::hasColumn('users', 'tpu')) {
            $kepalaBungus->update(['tpu' => 'TPU Bungus Teluk Kabung']);
        }

        // Petugas untuk TPU Tunggul Hitam
        $petugas1 = User::updateOrCreate(
            ['email' => 'petugas1@tpu.test'],
            [
                'name' => 'Petugas Tunggul Hitam',
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
    }
}
