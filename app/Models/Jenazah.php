<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Permohonan;

class Jenazah extends Model
{
    use HasFactory;

    protected $table = 'jenazah';

    protected $fillable = [
        'nama',
        'nik',
        'jenis_kelamin',
        'agama',
        'tempat_lahir',
        'tanggal_lahir',
        'tanggal_wafat',
        'alamat',
        'keterangan',
        'makam_id',
        'tpu',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_wafat' => 'date',
    ];

    // Relasi: Jenazah punya 1 makam
    public function makam()
    {
        return $this->belongsTo(Makam::class, 'makam_id');
    }

    public function permohonan()
    {
        return $this->hasOne(Permohonan::class, 'jenazah_id')->latestOfMany();
    }

    protected static function booted(): void
    {
        static::saved(function (Jenazah $jenazah) {
            if ($jenazah->makam_id) {
                $jenazah->makam?->syncStatusFromJenazah();
            }

            if ($jenazah->wasChanged('makam_id') && $jenazah->getOriginal('makam_id')) {
                Makam::find($jenazah->getOriginal('makam_id'))?->syncStatusFromJenazah();
            }
        });

        static::deleted(function (Jenazah $jenazah) {
            if ($jenazah->makam_id) {
                Makam::find($jenazah->makam_id)?->syncStatusFromJenazah();
            }
        });
    }
}
