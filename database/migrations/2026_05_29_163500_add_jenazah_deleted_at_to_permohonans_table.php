<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            if (! Schema::hasColumn('permohonans', 'jenazah_deleted_at')) {
                $table->timestamp('jenazah_deleted_at')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            if (Schema::hasColumn('permohonans', 'jenazah_deleted_at')) {
                $table->dropColumn('jenazah_deleted_at');
            }
        });
    }
};
