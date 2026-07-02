<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenazah', function (Blueprint $table) {
            if (! Schema::hasColumn('jenazah', 'nama_ahli_waris')) {
                $table->string('nama_ahli_waris')->nullable()->after('alamat');
            }

            if (! Schema::hasColumn('jenazah', 'no_hp_ahli_waris')) {
                $table->string('no_hp_ahli_waris', 50)->nullable()->after('nama_ahli_waris');
            }

            if (! Schema::hasColumn('jenazah', 'hubungan_keluarga')) {
                $table->string('hubungan_keluarga')->nullable()->after('no_hp_ahli_waris');
            }

            if (! Schema::hasColumn('jenazah', 'catatan')) {
                $table->text('catatan')->nullable()->after('hubungan_keluarga');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jenazah', function (Blueprint $table) {
            if (Schema::hasColumn('jenazah', 'catatan')) {
                $table->dropColumn('catatan');
            }

            if (Schema::hasColumn('jenazah', 'hubungan_keluarga')) {
                $table->dropColumn('hubungan_keluarga');
            }

            if (Schema::hasColumn('jenazah', 'no_hp_ahli_waris')) {
                $table->dropColumn('no_hp_ahli_waris');
            }

            if (Schema::hasColumn('jenazah', 'nama_ahli_waris')) {
                $table->dropColumn('nama_ahli_waris');
            }
        });
    }
};
