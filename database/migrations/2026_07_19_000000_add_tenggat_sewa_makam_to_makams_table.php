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
        Schema::table('makams', function (Blueprint $table) {
            if (! Schema::hasColumn('makams', 'tenggat_sewa_makam')) {
                // Menyimpan tenggat sewa "aktif" untuk makam ini.
                // Untuk makam tumpang sari (banyak jenazah dalam satu makam),
                // nilai ini selalu disinkronkan dari data jenazah TERBARU
                // (lihat Makam::syncTenggatSewaFromJenazah()).
                $table->date('tenggat_sewa_makam')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('makams', function (Blueprint $table) {
            if (Schema::hasColumn('makams', 'tenggat_sewa_makam')) {
                $table->dropColumn('tenggat_sewa_makam');
            }
        });
    }
};