<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jenazah extends Model
{
    use HasFactory;

    protected $table = 'jenazahs';

    protected $fillable = [
        'nama',
        'nik',
        'jenis_kelamin',
        'tanggal_lahir',
        'tanggal_wafat',
        'alamat',
        'keterangan',
        'makam_id',
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
}
