<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'kdlh', 'petugas', 'kepala', 'user') NOT NULL DEFAULT 'user'");
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'kdlh')->update(['role' => 'admin']);

        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'petugas', 'kepala', 'user') NOT NULL DEFAULT 'user'");
    }
};
