<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tpu extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'lokasi',
        'ringkasan',
        'highlight',
        'deskripsi',
        'wa_petugas_id',
    ];

    public function waPetugas()
    {
        return $this->belongsTo(User::class, 'wa_petugas_id');
    }

    public function biayaSewas()
    {
        return $this->hasMany(TpuBiayaSewa::class);
    }

    public static function options(): array
    {
        $names = static::query()
            ->orderBy('nama')
            ->pluck('nama')
            ->filter()
            ->values()
            ->all();

        if (! empty($names)) {
            return $names;
        }

        return [
            'TPU Tunggul Hitam',
            'TPU Bungus Teluk Kabung',
            'TPU Air Dingin',
        ];
    }
}