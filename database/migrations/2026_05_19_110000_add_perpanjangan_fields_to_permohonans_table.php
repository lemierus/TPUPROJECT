<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->string('no_makam')->nullable()->after('makam_id');
            $table->string('blok_zona_makam')->nullable()->after('no_makam');
            $table->year('tahun_pemakaman')->nullable()->after('blok_zona_makam');
            $table->string('bukti_pembayaran_retribusi')->nullable()->after('surat_kematian');
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropColumn([
                'no_makam',
                'blok_zona_makam',
                'tahun_pemakaman',
                'bukti_pembayaran_retribusi',
            ]);
        });
    }
};
