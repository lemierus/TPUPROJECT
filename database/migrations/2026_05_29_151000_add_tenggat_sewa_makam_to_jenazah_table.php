<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenazah', function (Blueprint $table) {
            if (! Schema::hasColumn('jenazah', 'tenggat_sewa_makam')) {
                $table->date('tenggat_sewa_makam')->nullable()->after('nomor_makam');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jenazah', function (Blueprint $table) {
            if (Schema::hasColumn('jenazah', 'tenggat_sewa_makam')) {
                $table->dropColumn('tenggat_sewa_makam');
            }
        });
    }
};
