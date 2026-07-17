<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE permohonans MODIFY COLUMN status ENUM(
            'menunggu',
            'disetujui',
            'ditolak',
            'menunggu_konfirmasi',
            'diproses_darurat',
            'administrasi_belum_lengkap',
            'menunggu_verifikasi_dokumen',
            'perlu_perbaikan_dokumen',
            'selesai'
        ) NOT NULL DEFAULT 'menunggu'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE permohonans MODIFY COLUMN status ENUM(
            'menunggu',
            'disetujui',
            'ditolak'
        ) NOT NULL DEFAULT 'menunggu'");
    }
};