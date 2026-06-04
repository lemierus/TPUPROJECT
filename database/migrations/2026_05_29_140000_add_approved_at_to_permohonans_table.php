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
            $table->timestamp('approved_at')->nullable()->after('status');
        });

        DB::table('permohonans')
            ->where('status', 'disetujui')
            ->whereNull('approved_at')
            ->update(['approved_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropColumn('approved_at');
        });
    }
};
