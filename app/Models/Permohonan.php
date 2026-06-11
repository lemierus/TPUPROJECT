<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class Permohonan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tpu',
        'jenis_permohonan',
        'nama_pemohon',
        'nama_jenazah',
        'nik_jenazah',
        'tempat_lahir',
        'alamat',
        'tanggal_lahir',
        'tanggal_wafat',
        'jenis_kelamin',
        'agama',
        'nama_ahli_waris',
        'no_hp_ahli_waris',
        'hubungan_keluarga',
        'scan_ktp_ahli_waris',
        'scan_kk',
        'surat_kematian',
        'jenazah_id',
        'makam_id',
        'tahun_pemakaman',
        'tenggat_sewa_makam',
        'bukti_pembayaran_retribusi',
        'status',
        'approved_at',
        'jenazah_deleted_at',
        'petugas_id',
        'catatan',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_wafat' => 'date',
        'tenggat_sewa_makam' => 'date',
        'approved_at' => 'datetime',
        'jenazah_deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /**
     * Try to resolve assigned petugas. If explicit petugas_id exists return it,
     * otherwise fall back to finding a petugas user for the TPU.
     */
    public function assignedPetugas(): ?User
    {
        if ($this->relationLoaded('petugas') && $this->petugas) {
            return $this->petugas;
        }

        if ($this->petugas_id) {
            return User::find($this->petugas_id);
        }

        if (! empty($this->tpu)) {
            return User::where('role', User::ROLE_PETUGAS)
                ->where('tpu', $this->tpu)
                ->first();
        }

        return null;
    }

    public function jenazah()
    {
        return $this->belongsTo(Jenazah::class);
    }

    public function makam()
    {
        return $this->belongsTo(Makam::class);
    }

    public function syncLinkedJenazahData(): void
    {
        $jenazah = $this->resolveLinkedJenazah();
        $makam = $this->makam ?: ($this->makam_id ? Makam::find($this->makam_id) : null);

        $sync = [
            'jenazah_id' => $jenazah?->id ?? $this->jenazah_id,
            'nama_jenazah' => $jenazah?->nama ?? $this->nama_jenazah,
            'nik_jenazah' => $jenazah?->nik ?? $this->nik_jenazah,
            'tempat_lahir' => $jenazah?->tempat_lahir ?? $this->tempat_lahir,
            'tanggal_lahir' => $jenazah?->tanggal_lahir ?? $this->tanggal_lahir,
            'tanggal_wafat' => $jenazah?->tanggal_wafat ?? $this->tanggal_wafat,
            'jenis_kelamin' => $jenazah?->jenis_kelamin ?? $this->jenis_kelamin,
            'agama' => $jenazah?->agama ?? $this->agama,
            'tenggat_sewa_makam' => $this->tenggat_sewa_makam ?? $jenazah?->tenggat_sewa_makam,
        ];

        if ($makam) {
            $sync['makam_id'] = $makam->id;
        }

        $this->fill(array_filter($sync, fn($value) => ! is_null($value)));

        if ($this->exists && $this->isDirty()) {
            $this->save();
        }
    }

    public function hasCompleteJenazahData(): bool
    {
        return $this->jenis_permohonan === 'makam_baru'
            && filled($this->nama_jenazah)
            && filled($this->nik_jenazah)
            && filled($this->jenis_kelamin)
            && filled($this->tanggal_wafat);
    }

    public function approvedAt(): ?CarbonInterface
    {
        if ($this->approved_at) {
            return $this->approved_at;
        }

        if ($this->status === 'disetujui' && $this->updated_at) {
            return $this->updated_at;
        }

        return null;
    }

    public function renewalDueAt(): ?CarbonInterface
    {
        if ($this->tenggat_sewa_makam) {
            return $this->tenggat_sewa_makam;
        }

        if ($this->relationLoaded('jenazah') && $this->jenazah?->tenggat_sewa_makam) {
            return $this->jenazah->tenggat_sewa_makam;
        }

        if ($this->jenazah_id) {
            $jenazah = Jenazah::find($this->jenazah_id);

            if ($jenazah?->tenggat_sewa_makam) {
                return $jenazah->tenggat_sewa_makam;
            }
        }

        if ($this->jenis_permohonan !== 'makam_baru' || $this->status !== 'disetujui') {
            return null;
        }

        $approvedAt = $this->approvedAt();

        return $approvedAt?->copy()->addYearsNoOverflow(2);
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

        if (Carbon::now()->diffInDays($dueAt) <= $warningDays) {
            return 'soon';
        }

        return 'safe';
    }

    public function persistJenazahRecord(): Jenazah
    {
        $makam = $this->resolveLinkedMakam();

        $jenazah = $this->resolveLinkedJenazah() ?? Jenazah::where('nik', $this->nik_jenazah)->first();

        if (! $jenazah) {
            $jenazah = new Jenazah();
        }

        $jenazah->fill($this->buildJenazahPayload($makam, $jenazah));
        $jenazah->save();

        $sync = [
            'jenazah_id' => $jenazah->id,
        ];

        if ($makam) {
            $sync['makam_id'] = $makam->id;
        }

        $this->fill($sync);
        $this->save();

        return $jenazah->fresh();
    }

    private function buildJenazahPayload(?Makam $makam = null, ?Jenazah $jenazah = null): array
    {
        $payload = [
            'nama' => $this->nama_jenazah,
            'nik' => $this->nik_jenazah,
            'jenis_kelamin' => $this->jenis_kelamin,
            'agama' => $this->agama,
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => $this->tanggal_lahir,
            'tanggal_wafat' => $this->tanggal_wafat,
            'alamat' => $this->alamat,
            'tpu' => $this->tpu,
        ];

        if ($makam) {
            $payload['makam_id'] = $makam->id;
        }

        return array_filter($payload, fn($value) => ! is_null($value));
    }

    private function resolveLinkedMakam(): ?Makam
    {
        return $this->makam ?: ($this->makam_id ? Makam::find($this->makam_id) : null);
    }

    private function resolveLinkedJenazah(): ?Jenazah
    {
        if ($this->relationLoaded('jenazah') && $this->jenazah) {
            return $this->jenazah;
        }

        if ($this->jenazah_id) {
            return Jenazah::find($this->jenazah_id);
        }

        if ($this->makam_id) {
            $byMakam = Jenazah::where('makam_id', $this->makam_id)->latest('id')->first();

            if ($byMakam) {
                return $byMakam;
            }
        }

        if ($this->nik_jenazah) {
            $byNik = Jenazah::where('nik', $this->nik_jenazah)->first();

            if ($byNik) {
                return $byNik;
            }
        }

        if ($this->nama_jenazah) {
            return Jenazah::where('nama', $this->nama_jenazah)->latest('id')->first();
        }

        return null;
    }

    protected static function booted(): void
    {
        static::saved(function (Permohonan $permohonan) {
            if (
                $permohonan->status === 'disetujui'
                && $permohonan->wasChanged('status')
                && $permohonan->hasCompleteJenazahData()
                && ! $permohonan->jenazah_id
            ) {
            $permohonan->persistJenazahRecord();
        }
    });

        static::saving(function (Permohonan $permohonan) {
            if ($permohonan->jenazah_deleted_at && $permohonan->isDirty('jenazah_deleted_at')) {
                $permohonan->jenazah_id = null;
            }
        });
    }
}
