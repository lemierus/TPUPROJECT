<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'kepala', 'user', 'petugas', 'kepala_upt', 'masyarakat') NOT NULL DEFAULT 'user'");

        DB::table('users')->where('role', 'petugas')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'kepala_upt')->update(['role' => 'kepala']);
        DB::table('users')->where('role', 'masyarakat')->update(['role' => 'user']);

        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'kepala', 'user') NOT NULL DEFAULT 'user'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'kepala', 'user', 'petugas', 'kepala_upt') NOT NULL DEFAULT 'petugas'");

        DB::table('users')->where('role', 'admin')->update(['role' => 'petugas']);
        DB::table('users')->where('role', 'kepala')->update(['role' => 'kepala_upt']);
        DB::table('users')->where('role', 'user')->update(['role' => 'petugas']);

        DB::statement("ALTER TABLE users MODIFY role ENUM('petugas', 'kepala_upt') NOT NULL DEFAULT 'petugas'");
    }
};
