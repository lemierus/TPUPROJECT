<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('makams', function (Blueprint $table) {
            $table->string('tpu')->nullable()->after('kode_makam');
        });

        Schema::table('permohonans', function (Blueprint $table) {
            $table->string('tpu')->nullable()->after('user_id');
        });

        DB::table('makams')->whereNull('tpu')->update(['tpu' => 'TPU Tunggul Hitam']);
        DB::table('permohonans')->whereNull('tpu')->update(['tpu' => 'TPU Tunggul Hitam']);
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropColumn('tpu');
        });

        Schema::table('makams', function (Blueprint $table) {
            $table->dropColumn('tpu');
        });
    }
};
