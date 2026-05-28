<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->string('nama_jenazah')->nullable()->after('jenis_permohonan');
            $table->string('nik_jenazah')->nullable()->after('nama_jenazah');
            $table->string('tempat_lahir')->nullable()->after('nik_jenazah');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->date('tanggal_wafat')->nullable()->after('tanggal_lahir');
            $table->string('jenis_kelamin')->nullable()->after('tanggal_wafat');
            $table->string('nama_ahli_waris')->nullable()->after('jenis_kelamin');
            $table->string('no_hp_ahli_waris')->nullable()->after('nama_ahli_waris');
            $table->string('hubungan_keluarga')->nullable()->after('no_hp_ahli_waris');
            $table->string('scan_ktp_ahli_waris')->nullable()->after('hubungan_keluarga');
            $table->string('scan_kk')->nullable()->after('scan_ktp_ahli_waris');
            $table->string('surat_kematian')->nullable()->after('scan_kk');
        });

        Schema::table('jenazah', function (Blueprint $table) {
            $table->string('tempat_lahir')->nullable()->after('jenis_kelamin');
        });
    }

    public function down(): void
    {
        Schema::table('jenazah', function (Blueprint $table) {
            $table->dropColumn('tempat_lahir');
        });

        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropColumn([
                'nama_jenazah',
                'nik_jenazah',
                'tempat_lahir',
                'tanggal_lahir',
                'tanggal_wafat',
                'jenis_kelamin',
                'nama_ahli_waris',
                'no_hp_ahli_waris',
                'hubungan_keluarga',
                'scan_ktp_ahli_waris',
                'scan_kk',
                'surat_kematian',
            ]);
        });
    }
};
