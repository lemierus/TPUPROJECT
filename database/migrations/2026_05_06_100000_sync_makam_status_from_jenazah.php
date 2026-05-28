<?php

use App\Models\Makam;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Makam::query()->each(function (Makam $makam) {
            $makam->syncStatusFromJenazah();
        });
    }

    public function down(): void
    {
        //
    }
};
