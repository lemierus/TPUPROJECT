<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            // Data Jenazah
            if (! Schema::hasColumn('permohonans', 'nama_jenazah')) {
                $table->string('nama_jenazah')->nullable()->after('jenis_permohonan');
            }
            if (! Schema::hasColumn('permohonans', 'nik_jenazah')) {
                $table->string('nik_jenazah')->nullable()->after('nama_jenazah');
            }
            if (! Schema::hasColumn('permohonans', 'tempat_lahir')) {
                $table->string('tempat_lahir')->nullable()->after('nik_jenazah');
            }
            if (! Schema::hasColumn('permohonans', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            }
            if (! Schema::hasColumn('permohonans', 'tanggal_wafat')) {
                $table->date('tanggal_wafat')->nullable()->after('tanggal_lahir');
            }
            if (! Schema::hasColumn('permohonans', 'jenis_kelamin')) {
                $table->string('jenis_kelamin')->nullable()->after('tanggal_wafat');
            }
            if (! Schema::hasColumn('permohonans', 'agama')) {
                $table->string('agama')->nullable()->after('jenis_kelamin');
            }

            // Data Ahli Waris
            if (! Schema::hasColumn('permohonans', 'nama_ahli_waris')) {
                $table->string('nama_ahli_waris')->nullable()->after('agama');
            }
            if (! Schema::hasColumn('permohonans', 'no_hp_ahli_waris')) {
                $table->string('no_hp_ahli_waris')->nullable()->after('nama_ahli_waris');
            }
            if (! Schema::hasColumn('permohonans', 'hubungan_keluarga')) {
                $table->string('hubungan_keluarga')->nullable()->after('no_hp_ahli_waris');
            }

            // Files
            if (! Schema::hasColumn('permohonans', 'scan_ktp_ahli_waris')) {
                $table->string('scan_ktp_ahli_waris')->nullable()->after('hubungan_keluarga');
            }
            if (! Schema::hasColumn('permohonans', 'scan_kk')) {
                $table->string('scan_kk')->nullable()->after('scan_ktp_ahli_waris');
            }
            if (! Schema::hasColumn('permohonans', 'surat_kematian')) {
                $table->string('surat_kematian')->nullable()->after('scan_kk');
            }

            // Foreign Key Jenazah
            if (! Schema::hasColumn('permohonans', 'jenazah_id')) {
                $table->unsignedBigInteger('jenazah_id')->nullable()->after('user_id');
            }

            // Perpanjangan Fields
            if (! Schema::hasColumn('permohonans', 'no_makam')) {
                $table->string('no_makam')->nullable()->after('makam_id');
            }
            if (! Schema::hasColumn('permohonans', 'blok_zona_makam')) {
                $table->string('blok_zona_makam')->nullable()->after('no_makam');
            }
            if (! Schema::hasColumn('permohonans', 'tahun_pemakaman')) {
                $table->year('tahun_pemakaman')->nullable()->after('blok_zona_makam');
            }
            if (! Schema::hasColumn('permohonans', 'bukti_pembayaran_retribusi')) {
                $table->string('bukti_pembayaran_retribusi')->nullable()->after('surat_kematian');
            }

            // Catatan (use keterangan if exists)
            if (! Schema::hasColumn('permohonans', 'catatan') && Schema::hasColumn('permohonans', 'keterangan')) {
                // Rename keterangan to catatan
                $table->renameColumn('keterangan', 'catatan');
            } elseif (! Schema::hasColumn('permohonans', 'catatan')) {
                $table->text('catatan')->nullable()->after('bukti_pembayaran_retribusi');
            }

            // Update status enum to include menunggu
            if (Schema::hasColumn('permohonans', 'status')) {
                DB::statement("ALTER TABLE permohonans MODIFY status ENUM('menunggu', 'pending', 'disetujui', 'ditolak') NOT NULL DEFAULT 'menunggu'");
            }
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $columns = [
                'nama_jenazah', 'nik_jenazah', 'tempat_lahir', 'tanggal_lahir', 'tanggal_wafat',
                'jenis_kelamin', 'agama', 'nama_ahli_waris', 'no_hp_ahli_waris', 'hubungan_keluarga',
                'scan_ktp_ahli_waris', 'scan_kk', 'surat_kematian', 'jenazah_id', 'no_makam',
                'blok_zona_makam', 'tahun_pemakaman', 'bukti_pembayaran_retribusi'
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('permohonans', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
