<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip')->nullable()->after('email');
            $table->string('no_hp')->nullable()->after('nip');
        });

        Schema::table('tpus', function (Blueprint $table) {
            $table->foreignId('wa_petugas_id')->nullable()->after('deskripsi')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'no_hp']);
        });

        Schema::table('tpus', function (Blueprint $table) {
            $table->dropForeign(['wa_petugas_id']);
            $table->dropColumn('wa_petugas_id');
        });
    }
};
