<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            if (! Schema::hasColumn('permohonans', 'tenggat_sewa_makam')) {
                $table->date('tenggat_sewa_makam')->nullable()->after('tahun_pemakaman');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            if (Schema::hasColumn('permohonans', 'tenggat_sewa_makam')) {
                $table->dropColumn('tenggat_sewa_makam');
            }
        });
    }
};
