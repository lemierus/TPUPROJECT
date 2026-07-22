<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->foreignId('biaya_retribusi_id')
                ->nullable()
                ->after('makam_id')
                ->constrained('biaya_retribusi')
                ->nullOnDelete();

            $table->string('bukti_transfer')->nullable()->after('biaya_retribusi_id');
            $table->string('status_pembayaran')->nullable()->after('bukti_transfer');
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('biaya_retribusi_id');
            $table->dropColumn(['bukti_transfer', 'status_pembayaran']);
        });
    }
};
