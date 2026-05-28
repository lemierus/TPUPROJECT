<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            if (! Schema::hasColumn('permohonans', 'jenis_permohonan')) {
                $table->enum('jenis_permohonan', ['makam_baru', 'perpanjangan'])
                    ->default('makam_baru')
                    ->after('user_id');
            }

            if (! Schema::hasColumn('permohonans', 'makam_id')) {
                $table->foreignId('makam_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('makams')
                    ->nullOnDelete();
            }
        });

        if (Schema::hasColumn('permohonans', 'jenazah_id')) {
            DB::statement('ALTER TABLE permohonans MODIFY jenazah_id BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('permohonans', 'jenazah_id')) {
            DB::table('permohonans')->whereNull('jenazah_id')->delete();
            DB::statement('ALTER TABLE permohonans MODIFY jenazah_id BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('permohonans', function (Blueprint $table) {
            if (Schema::hasColumn('permohonans', 'makam_id')) {
                $table->dropForeign(['makam_id']);
                $table->dropColumn('makam_id');
            }

            if (Schema::hasColumn('permohonans', 'jenis_permohonan')) {
                $table->dropColumn('jenis_permohonan');
            }
        });
    }
};
