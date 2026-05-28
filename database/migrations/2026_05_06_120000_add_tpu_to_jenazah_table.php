<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenazah', function (Blueprint $table) {
            $table->string('tpu')->nullable()->after('makam_id');
        });

        DB::table('jenazah')->whereNull('tpu')->update(['tpu' => 'TPU Tunggul Hitam']);
        DB::statement('UPDATE jenazah SET tpu = (SELECT tpu FROM makams WHERE makams.id = jenazah.makam_id) WHERE makam_id IS NOT NULL AND tpu = ?', ['TPU Tunggul Hitam']);
    }

    public function down(): void
    {
        Schema::table('jenazah', function (Blueprint $table) {
            $table->dropColumn('tpu');
        });
    }
};
