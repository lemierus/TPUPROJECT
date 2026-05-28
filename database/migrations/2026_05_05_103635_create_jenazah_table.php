<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenazah', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nik')->unique();
            $table->string('jenis_kelamin');
            $table->date('tanggal_lahir')->nullable();
            $table->date('tanggal_wafat');
            $table->text('alamat')->nullable();
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('makam_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenazah');
    }
};