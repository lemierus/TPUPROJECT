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
    Schema::create('jenazahs', function (Blueprint $table) {
        $table->id();
        $table->string('nama');
        $table->string('nik')->nullable();
        $table->string('jenis_kelamin')->nullable();
        $table->date('tanggal_lahir')->nullable();
        $table->date('tanggal_wafat')->nullable();
        $table->text('alamat')->nullable();
        $table->text('keterangan')->nullable();

        $table->foreignId('makam_id')->nullable()->constrained('makams')->nullOnDelete();

        $table->timestamps();
    });
}
};
