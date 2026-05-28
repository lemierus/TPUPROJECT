<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_PETUGAS = 'petugas';
    public const ROLE_KEPALA = 'kepala';
    public const ROLE_USER = 'user';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'tpu',
        'profile_photo_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relasi ke Permohonan (User sebagai pemohon)
    public function permohonans()
    {
        return $this->hasMany(Permohonan::class, 'user_id');
    }

    // Relasi ke Permohonan (User sebagai petugas)
    public function permohonansTangani()
    {
        return $this->hasMany(Permohonan::class, 'petugas_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isKepala(): bool
    {
        return $this->role === self::ROLE_KEPALA;
    }

    public function isPetugas(): bool
    {
        return $this->role === self::ROLE_PETUGAS;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public static function tpuOptions(): array
    {
        return [
            'TPU Tunggul Hitam',
            'TPU Bungus Teluk Kabung',
            'TPU Air Dingin',
        ];
    }
}
