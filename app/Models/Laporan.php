<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $fillable = [
        'nama_jenazah',
        'jenis_kelamin',
        'tanggal_wafat',
        'makam',
        'blok',
        'zona',
        'periode',
    ];
}