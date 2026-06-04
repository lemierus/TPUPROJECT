<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Permohonan;
use Carbon\CarbonInterface;

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
        'kode_makam',
        'blok',
        'zona',
        'nomor_makam',
        'tenggat_sewa_makam',
        'tpu',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_wafat' => 'date',
        'tenggat_sewa_makam' => 'date',
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

    public function renewalDueAt(): ?CarbonInterface
    {
        if ($this->tenggat_sewa_makam) {
            return $this->tenggat_sewa_makam;
        }

        return $this->permohonan?->renewalDueAt();
    }

    public function renewalAlertLevel(int $warningDays = 90): ?string
    {
        $dueAt = $this->renewalDueAt();

        if (! $dueAt) {
            return null;
        }

        if ($dueAt->isPast()) {
            return 'expired';
        }

        if (now()->diffInDays($dueAt) <= $warningDays) {
            return 'soon';
        }

        return 'safe';
    }

    protected static function booted(): void
    {
        static::saved(function (Jenazah $jenazah) {
            $makam = $jenazah->makam()->first();

            if ($makam) {
                $updates = array_filter([
                    'kode_makam' => $makam->kode_makam,
                    'blok' => $makam->blok,
                    'zona' => $makam->zona,
                    'nomor_makam' => $makam->nomor,
                ], fn($value) => ! is_null($value));

                $syncNeeded = collect($updates)->contains(function ($value, $key) use ($jenazah) {
                    return $jenazah->{$key} !== $value;
                });

                if ($syncNeeded) {
                    $jenazah->withoutEvents(function () use ($jenazah, $updates) {
                        $jenazah->forceFill($updates)->save();
                    });
                }

                $makam->syncStatusFromJenazah();
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
