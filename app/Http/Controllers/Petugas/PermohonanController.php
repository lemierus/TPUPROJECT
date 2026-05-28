<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Makam;
use App\Models\Permohonan;
use App\Models\Jenazah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PermohonanController extends Controller
{
    public function index()
    {
        $petugas = auth()->user();

        $permohonans = Permohonan::with(['user', 'jenazah', 'makam'])
            ->where('tpu', $petugas->tpu)
            ->latest('created_at')
            ->get();

        $permohonans->each(function (Permohonan $permohonan) {
            $permohonan->syncLinkedJenazahData();
        });

        $stats = [
            'menunggu' => Permohonan::where('tpu', $petugas->tpu)
                ->whereIn('status', ['pending', 'menunggu'])
                ->count(),
            'disetujui' => Permohonan::where('tpu', $petugas->tpu)
                ->where('status', 'disetujui')
                ->count(),
            'ditolak' => Permohonan::where('tpu', $petugas->tpu)
                ->where('status', 'ditolak')
                ->count(),
            'total' => Permohonan::where('tpu', $petugas->tpu)->count(),
        ];

        return view('petugas.permohonan.index', compact('permohonans', 'stats', 'petugas'));
    }

    public function show(Permohonan $permohonan)
    {
        $this->authorizePermohonan($permohonan);
        $permohonan->syncLinkedJenazahData();

        return view('petugas.permohonan.show', compact('permohonan'));
    }

    public function edit(Permohonan $permohonan)
    {
        $this->authorizePermohonan($permohonan);
        $permohonan->syncLinkedJenazahData();

        $makams = Makam::where('tpu', $permohonan->tpu)->orderBy('kode_makam')->get();

        return view('petugas.permohonan.edit', compact('permohonan', 'makams'));
    }

    public function update(Request $request, Permohonan $permohonan)
    {
        $this->authorizePermohonan($permohonan);

        $data = $request->validate([
            'nama_jenazah' => ['nullable', 'string', 'max:255'],
            'nik_jenazah' => ['nullable', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'tanggal_wafat' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'string'],
            'agama' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'nama_ahli_waris' => ['nullable', 'string', 'max:255'],
            'no_hp_ahli_waris' => ['nullable', 'string', 'max:30'],
            'hubungan_keluarga' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
        ]);

        $permohonan->update($data);

        return redirect()->route('petugas.permohonan.show', $permohonan)
            ->with('success', 'Data permohonan berhasil diperbarui.');
    }

    public function approve(Request $request, Permohonan $permohonan)
    {
        $this->authorizePermohonan($permohonan);

        $request->validate([
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use ($request, $permohonan) {
                $permohonan->status = 'disetujui';
                if ($request->filled('catatan')) {
                    $permohonan->catatan = $request->catatan;
                }
                $permohonan->save();

                \Log::info('Permohonan disetujui', [
                    'id' => $permohonan->id,
                    'jenis_permohonan' => $permohonan->jenis_permohonan,
                    'jenazah_id' => $permohonan->jenazah_id,
                    'nama_jenazah' => $permohonan->nama_jenazah,
                    'nik_jenazah' => $permohonan->nik_jenazah,
                ]);

                if ($permohonan->jenis_permohonan === 'makam_baru' && !$permohonan->jenazah_id) {
                    \Log::info('Memulai create jenazah dari permohonan', ['permohonan_id' => $permohonan->id]);
                    $this->createJenazahFromPermohonan($permohonan);
                    \Log::info('Jenazah berhasil dibuat', ['jenazah_id' => $permohonan->jenazah_id]);
                }
            });
        } catch (ValidationException $e) {
            \Log::warning('ValidationException saat approve', ['errors' => $e->errors(), 'permohonan_id' => $permohonan->id]);
            return redirect()->route('petugas.permohonan.show', $permohonan)
                ->withErrors($e->errors())
                ->with('error', collect($e->errors())->flatten()->first() ?? 'Gagal menyetujui permohonan.');
        } catch (\Exception $e) {
            \Log::error('Error saat approve permohonan', ['message' => $e->getMessage(), 'permohonan_id' => $permohonan->id]);
            return redirect()->route('petugas.permohonan.show', $permohonan)
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->route('petugas.permohonan')
            ->with('success', 'Permohonan berhasil disetujui dan data jenazah tersimpan.');
    }

    public function reject(Request $request, Permohonan $permohonan)
    {
        $this->authorizePermohonan($permohonan);

        $request->validate([
            'catatan' => ['required', 'string', 'max:1000'],
        ]);

        $permohonan->update([
            'status' => 'ditolak',
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('petugas.permohonan')
            ->with('success', 'Permohonan berhasil ditolak.');
    }

    private function authorizePermohonan(Permohonan $permohonan): void
    {
        abort_unless(
            $permohonan->tpu === auth()->user()->tpu,
            403,
            'Anda tidak memiliki akses ke permohonan ini.'
        );
    }

    private function createJenazahFromPermohonan(Permohonan $permohonan): void
    {
        if (empty($permohonan->nik_jenazah)) {
            throw ValidationException::withMessages([
                'status' => 'Permohonan tidak bisa disetujui karena NIK jenazah belum diisi. Silahkan edit permohonan terlebih dahulu.',
            ]);
        }

        if (Jenazah::where('nik', $permohonan->nik_jenazah)->exists()) {
            throw ValidationException::withMessages([
                'status' => 'Permohonan tidak bisa disetujui karena NIK jenazah sudah terdaftar di data jenazah.',
            ]);
        }

        if (empty($permohonan->nama_jenazah)) {
            throw ValidationException::withMessages([
                'status' => 'Permohonan tidak bisa disetujui karena nama jenazah belum diisi. Silahkan edit permohonan terlebih dahulu.',
            ]);
        }

        $jenazah = Jenazah::create([
            'nama' => $permohonan->nama_jenazah,
            'nik' => $permohonan->nik_jenazah,
            'jenis_kelamin' => $permohonan->jenis_kelamin,
            'agama' => $permohonan->agama,
            'tempat_lahir' => $permohonan->tempat_lahir,
            'tanggal_lahir' => $permohonan->tanggal_lahir,
            'tanggal_wafat' => $permohonan->tanggal_wafat,
            'tpu' => $permohonan->tpu,
        ]);

        $permohonan->update([
            'jenazah_id' => $jenazah->id,
        ]);
    }
}
