<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tpu_biaya_sewas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tpu_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->unsignedBigInteger('harga');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tpu_biaya_sewas');
    }
};