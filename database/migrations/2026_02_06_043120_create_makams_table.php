<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('makams', function (Blueprint $table) {
        $table->id();
        $table->string('kode_makam')->unique();
        $table->string('blok')->nullable();
        $table->string('zona')->nullable();
        $table->string('nomor')->nullable();
        $table->string('status')->default('kosong'); // kosong / terisi
        $table->text('keterangan')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('makams');
    }
};
