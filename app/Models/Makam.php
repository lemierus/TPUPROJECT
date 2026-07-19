<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Carbon\CarbonInterface;

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
        'tenggat_sewa_makam',
    ];

    protected $casts = [
        'tenggat_sewa_makam' => 'date',
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

    /**
     * Sinkronkan tenggat_sewa_makam pada makam ini dengan tenggat_sewa_makam
     * milik jenazah TERBARU yang menempati makam ini.
     *
     * "Terbaru" ditentukan dari tanggal_wafat paling baru; jika tanggal_wafat
     * sama/kosong, jenazah dengan id terbesar (data terakhir dibuat) dipakai
     * sebagai fallback. Berguna untuk makam tumpang sari yang diisi lebih
     * dari satu jenazah — tenggat sewa makam selalu mengikuti jenazah yang
     * paling akhir dimakamkan di sana.
     */
    public function syncTenggatSewaFromJenazah(): void
    {
        if (! Schema::hasColumn('makams', 'tenggat_sewa_makam')) {
            return;
        }

        $latestJenazah = $this->jenazahs()
            ->orderByDesc('tanggal_wafat')
            ->orderByDesc('id')
            ->first();

        $this->update([
            'tenggat_sewa_makam' => $latestJenazah?->tenggat_sewa_makam,
        ]);
    }

    public function applyRenewalDueAtToJenazahs(CarbonInterface|string $dueAt): void
    {
        if (! Schema::hasColumn('makams', 'tenggat_sewa_makam')) {
            return;
        }

        $this->jenazahs()->update([
            'tenggat_sewa_makam' => $dueAt,
        ]);

        $this->update([
            'tenggat_sewa_makam' => $dueAt,
        ]);
    }

    /**
     * Sinkronkan status ("kosong"/"terisi") sekaligus tenggat_sewa_makam
     * makam ini berdasarkan data jenazah yang menempatinya saat ini.
     * Panggil method ini setiap kali data jenazah pada makam ini
     * dibuat, diubah, atau dihapus.
     */
    public function syncFromJenazah(): void
    {
        $this->syncStatusFromJenazah();
        $this->syncTenggatSewaFromJenazah();
    }

    // ===== TAMBAHAN: jumlah jenazah dalam satu makam =====
    // Berguna untuk ditampilkan ke petugas saat memilih makam tujuan tumpang sari,
    // supaya petugas tahu makam tersebut sudah berisi berapa jenazah.
    public function jumlahJenazah(): int
    {
        return $this->jenazahs()->count();
    }
}
