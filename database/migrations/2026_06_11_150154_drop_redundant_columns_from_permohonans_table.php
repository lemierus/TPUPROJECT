<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropColumn([
                'nik_pemohon',
                'kode_makam',
                'blok',
                'zona',
                'nomor_makam',
                'keterangan',
                'no_makam',
                'blok_zona_makam',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->string('nik_pemohon')->nullable()->after('nama_pemohon');
            $table->string('kode_makam')->nullable()->after('makam_id');
            $table->string('blok')->nullable()->after('kode_makam');
            $table->string('zona')->nullable()->after('blok');
            $table->string('nomor_makam')->nullable()->after('zona');
            $table->text('keterangan')->nullable()->after('nomor_makam');
            $table->string('no_makam')->nullable()->after('keterangan');
            $table->string('blok_zona_makam')->nullable()->after('no_makam');
        });
    }
};
