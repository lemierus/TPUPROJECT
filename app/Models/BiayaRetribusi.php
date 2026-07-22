<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiayaRetribusi extends Model
{
    use HasFactory;

    protected $table = 'biaya_retribusi';

    protected $fillable = [
        'nama_biaya',
        'nominal',
        'nomor_rekening',
        'nama_bank',
        'atas_nama_rekening',
        'is_aktif',
    ];

    protected $casts = [
        'nominal' => 'integer',
        'is_aktif' => 'boolean',
    ];

    public function permohonans()
    {
        return $this->hasMany(Permohonan::class, 'biaya_retribusi_id');
    }

    public function isGratis(): bool
    {
        return (int) $this->nominal === 0;
    }
}
