<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Makam extends Model
{
    use HasFactory;

    protected $table = 'makams';

    protected $fillable = [
        'tpu',
        'kode_makam',
        'blok',
        'zona',
        'nomor',
        'status',
        'keterangan',
    ];

    // Relasi: Makam bisa memiliki banyak jenazah (jika sistem memungkinkan)
    public function jenazahs()
    {
        return $this->hasMany(Jenazah::class, 'makam_id');
    }

    // Relasi: Makam bisa muncul di permohonan
    public function permohonans()
    {
        return $this->hasMany(Permohonan::class, 'makam_id');
    }

    public function syncStatusFromJenazah(): void
    {
        $this->update([
            'status' => $this->jenazahs()->exists() ? 'terisi' : 'kosong',
        ]);
    }
}
