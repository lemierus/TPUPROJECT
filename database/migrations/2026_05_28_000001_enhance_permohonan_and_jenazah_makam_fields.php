<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->string('kode_makam')->nullable()->after('makam_id');
            $table->string('blok')->nullable()->after('kode_makam');
            $table->string('zona')->nullable()->after('blok');
            $table->string('nomor_makam')->nullable()->after('zona');
            $table->text('keterangan')->nullable()->after('nomor_makam');
        });

        Schema::table('jenazah', function (Blueprint $table) {
            $table->string('kode_makam')->nullable()->after('makam_id');
            $table->string('blok')->nullable()->after('kode_makam');
            $table->string('zona')->nullable()->after('blok');
            $table->string('nomor_makam')->nullable()->after('zona');
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropColumn(['kode_makam', 'blok', 'zona', 'nomor_makam', 'keterangan']);
        });

        Schema::table('jenazah', function (Blueprint $table) {
            $table->dropColumn(['kode_makam', 'blok', 'zona', 'nomor_makam']);
        });
    }
};
