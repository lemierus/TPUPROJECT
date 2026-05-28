<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            if (!Schema::hasColumn('permohonans', 'jenazah_id')) {
                $table->unsignedBigInteger('jenazah_id')->nullable();
            }
            $table->foreign('jenazah_id')->references('id')->on('jenazah')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropForeign(['jenazah_id']);
        });
    }
};
