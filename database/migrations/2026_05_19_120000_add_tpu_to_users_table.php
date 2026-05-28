<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tpu')->nullable()->after('role');
        });

        DB::table('users')
            ->where('role', User::ROLE_PETUGAS)
            ->whereNull('tpu')
            ->update(['tpu' => 'TPU Tunggul Hitam']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tpu');
        });
    }
};
