<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->string('agama')->nullable()->after('jenis_kelamin');
        });

        Schema::table('jenazah', function (Blueprint $table) {
            $table->string('agama')->nullable()->after('jenis_kelamin');
        });
    }

    public function down(): void
    {
        Schema::table('jenazah', function (Blueprint $table) {
            $table->dropColumn('agama');
        });

        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropColumn('agama');
        });
    }
};
