<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            if (! Schema::hasColumn('permohonans', 'catatan_revisi')) {
                $table->text('catatan_revisi')->nullable()->after('catatan');
            }
        });

        Schema::table('permohonans', function (Blueprint $table) {
            $nullableColumns = [
                'nama_jenazah',
                'jenis_kelamin',
                'agama',
                'tanggal_wafat',
                'nama_ahli_waris',
                'no_hp_ahli_waris',
                'hubungan_keluarga',
                'tpu',
                'catatan',
                'nik_jenazah',
                'scan_ktp_ahli_waris',
                'scan_kk',
                'surat_kematian',
                'tempat_lahir',
                'tanggal_lahir',
            ];

            foreach ($nullableColumns as $column) {
                if (Schema::hasColumn('permohonans', $column)) {
                    try {
                        $table->string($column)->nullable()->change();
                    } catch (\Throwable $e) {
                        // Ignore string redefinition errors and let specific types below handle them.
                    }
                }
            }

            if (Schema::hasColumn('permohonans', 'tanggal_wafat')) {
                $table->date('tanggal_wafat')->nullable()->change();
            }

            if (Schema::hasColumn('permohonans', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable()->change();
            }

            if (Schema::hasColumn('permohonans', 'catatan')) {
                $table->text('catatan')->nullable()->change();
            }

            if (Schema::hasColumn('permohonans', 'alamat')) {
                $table->text('alamat')->nullable()->change();
            }
        });

        DB::statement("
            ALTER TABLE permohonans
            MODIFY status ENUM(
                'pending',
                'menunggu',
                'menunggu_konfirmasi',
                'diproses_darurat',
                'administrasi_belum_lengkap',
                'menunggu_verifikasi_dokumen',
                'perlu_perbaikan_dokumen',
                'disetujui',
                'ditolak',
                'selesai'
            ) NOT NULL DEFAULT 'menunggu'
        ");
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            if (Schema::hasColumn('permohonans', 'catatan_revisi')) {
                $table->dropColumn('catatan_revisi');
            }
        });

        DB::statement("
            ALTER TABLE permohonans
            MODIFY status ENUM('menunggu', 'pending', 'disetujui', 'ditolak') NOT NULL DEFAULT 'menunggu'
        ");
    }
};
