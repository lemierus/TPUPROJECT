<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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
        'no_makam',
        'blok_zona_makam',
        'tahun_pemakaman',
        'bukti_pembayaran_retribusi',
        'status',
        'petugas_id',
        'catatan',
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
        if ($this->jenis_permohonan !== 'perpanjangan') {
            return;
        }

        $jenazah = $this->resolveLinkedJenazah();

        if (! $jenazah) {
            return;
        }

        $this->fill([
            'jenazah_id' => $jenazah->id,
            'nama_jenazah' => $jenazah->nama,
            'nik_jenazah' => $jenazah->nik,
            'tempat_lahir' => $jenazah->tempat_lahir,
            'tanggal_lahir' => $jenazah->tanggal_lahir,
            'tanggal_wafat' => $jenazah->tanggal_wafat,
            'jenis_kelamin' => $jenazah->jenis_kelamin,
            'agama' => $jenazah->agama,
        ]);

        if ($this->exists && $this->isDirty()) {
            $this->save();
        }
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
}
