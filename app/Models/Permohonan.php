<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permohonan extends Model
{
    use HasFactory;

    protected $table = 'permohonans';

    protected $fillable = [
        'user_id',
        'makam_id',
        'nama_pemohon',
        'nik_pemohon',
        'jenis_permohonan',
        'tanggal_permohonan',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_permohonan' => 'date',
    ];

    // Relasi: Permohonan milik user (pemohon)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi: Permohonan bisa terkait makam
    public function makam()
    {
        return $this->belongsTo(Makam::class, 'makam_id');
    }
}
