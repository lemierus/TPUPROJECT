<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biaya_retribusi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_biaya');
            $table->unsignedBigInteger('nominal')->default(0);
            $table->string('nomor_rekening');
            $table->string('nama_bank');
            $table->string('atas_nama_rekening');
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biaya_retribusi');
    }
};
