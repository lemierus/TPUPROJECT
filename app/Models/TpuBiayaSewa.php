<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TpuBiayaSewa extends Model
{
    use HasFactory;

    protected $fillable = [
        'tpu_id',
        'label',
        'harga',
    ];

    protected $casts = [
        'harga' => 'integer',
    ];

    public function tpu()
    {
        return $this->belongsTo(Tpu::class);
    }
}