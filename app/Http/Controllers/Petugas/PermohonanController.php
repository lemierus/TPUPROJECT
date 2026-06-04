<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Makam;
use App\Models\Permohonan;
use App\Models\Jenazah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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

        $perpanjanganPerluDiingatkan = Permohonan::with(['user', 'jenazah', 'makam'])
            ->where('tpu', $petugas->tpu)
            ->where('status', 'disetujui')
            ->where('jenis_permohonan', 'makam_baru')
            ->get()
            ->filter(function (Permohonan $permohonan) {
                $level = $permohonan->renewalAlertLevel();

                return in_array($level, ['soon', 'expired'], true);
            })
            ->sortBy(function (Permohonan $permohonan) {
                return $permohonan->renewalDueAt()?->timestamp ?? PHP_INT_MAX;
            })
            ->values();

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

        return view('petugas.permohonan.index', compact('permohonans', 'stats', 'petugas', 'perpanjanganPerluDiingatkan'));
    }

    public function create()
    {
        $petugas = auth()->user();

        $makams = Makam::where('tpu', $petugas->tpu)
            ->orderBy('kode_makam')
            ->get();

        return view('petugas.permohonan.create', [
            'petugas' => $petugas,
            'makams' => $makams,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis_permohonan' => ['required', Rule::in(['makam_baru', 'perpanjangan'])],
            'nama_jenazah' => ['nullable', 'string', 'max:255'],
            'nik_jenazah' => ['nullable', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'tanggal_wafat' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
            'agama' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'nama_ahli_waris' => ['required', 'string', 'max:255'],
            'no_hp_ahli_waris' => ['required', 'string', 'max:30'],
            'hubungan_keluarga' => ['required', 'string', 'max:255'],
            'makam_id' => ['nullable', 'exists:makams,id'],
            'kode_makam' => ['nullable', 'string', 'max:255'],
            'blok' => ['nullable', 'string', 'max:255'],
            'zona' => ['nullable', 'string', 'max:255'],
            'nomor_makam' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'no_makam' => ['nullable', 'string', 'max:255'],
            'blok_zona_makam' => ['nullable', 'string', 'max:255'],
            'tahun_pemakaman' => ['nullable', 'string', 'max:255'],
            'scan_ktp_ahli_waris' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'scan_kk' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'surat_kematian' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'bukti_pembayaran_retribusi' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'catatan' => ['nullable', 'string'],
        ]);

        $validator->sometimes(['nama_jenazah', 'nik_jenazah', 'tanggal_wafat', 'jenis_kelamin', 'agama', 'tempat_lahir', 'tanggal_lahir', 'alamat'], 'required', function ($input) {
            return $input->jenis_permohonan === 'makam_baru';
        });

        $validator->sometimes(['makam_id', 'no_makam', 'blok_zona_makam'], 'required', function ($input) {
            return $input->jenis_permohonan === 'perpanjangan';
        });

        $data = $validator->validate();

        $petugas = auth()->user();
        $selectedMakam = $data['makam_id'] ? Makam::find($data['makam_id']) : null;

        $paths = [
            'scan_ktp_ahli_waris' => $request->file('scan_ktp_ahli_waris')->store('permohonan/ktp', 'public'),
            'scan_kk' => $request->file('scan_kk')->store('permohonan/kk', 'public'),
            'surat_kematian' => $request->file('surat_kematian')->store('permohonan/surat-kematian', 'public'),
            'bukti_pembayaran_retribusi' => $request->hasFile('bukti_pembayaran_retribusi')
                ? $request->file('bukti_pembayaran_retribusi')->store('permohonan/retribusi', 'public')
                : null,
        ];

        $permohonan = Permohonan::create(array_merge($data, [
            'user_id' => $petugas->id,
            'petugas_id' => $petugas->id,
            'tpu' => $petugas->tpu,
            'nama_pemohon' => $petugas->name,
            'scan_ktp_ahli_waris' => $paths['scan_ktp_ahli_waris'],
            'scan_kk' => $paths['scan_kk'],
            'surat_kematian' => $paths['surat_kematian'],
            'bukti_pembayaran_retribusi' => $paths['bukti_pembayaran_retribusi'],
            'status' => 'menunggu',
            'makam_id' => $selectedMakam?->id ?? null,
            'kode_makam' => $selectedMakam?->kode_makam ?? $data['kode_makam'] ?? null,
            'blok' => $selectedMakam?->blok ?? $data['blok'] ?? null,
            'zona' => $selectedMakam?->zona ?? $data['zona'] ?? null,
            'nomor_makam' => $selectedMakam?->nomor ?? $data['nomor_makam'] ?? null,
            'keterangan' => $data['keterangan'] ?? $selectedMakam?->keterangan ?? null,
            'no_makam' => $selectedMakam?->nomor ?? $data['no_makam'] ?? null,
            'blok_zona_makam' => $selectedMakam ? trim(implode(' / ', array_filter([$selectedMakam->blok, $selectedMakam->zona])), ' /') : ($data['blok_zona_makam'] ?? null),
            'tahun_pemakaman' => $data['tahun_pemakaman'] ?? null,
        ]));

        return redirect()->route('petugas.permohonan.show', $permohonan)
            ->with('success', 'Permohonan berhasil dibuat.');
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

        $validator = Validator::make($request->all(), [
            'jenis_permohonan' => ['required', Rule::in(['makam_baru', 'perpanjangan'])],
            'nama_jenazah' => ['nullable', 'string', 'max:255'],
            'nik_jenazah' => ['nullable', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'tanggal_wafat' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
            'agama' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'nama_ahli_waris' => ['nullable', 'string', 'max:255'],
            'no_hp_ahli_waris' => ['nullable', 'string', 'max:30'],
            'hubungan_keluarga' => ['nullable', 'string', 'max:255'],
            'makam_id' => ['nullable', 'exists:makams,id'],
            'kode_makam' => ['nullable', 'string', 'max:255'],
            'blok' => ['nullable', 'string', 'max:255'],
            'zona' => ['nullable', 'string', 'max:255'],
            'nomor_makam' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'no_makam' => ['nullable', 'string', 'max:255'],
            'blok_zona_makam' => ['nullable', 'string', 'max:255'],
            'tahun_pemakaman' => ['nullable', 'string', 'max:255'],
            'scan_ktp_ahli_waris' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'scan_kk' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'surat_kematian' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'bukti_pembayaran_retribusi' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'catatan' => ['nullable', 'string'],
        ]);

        $validator->sometimes(['nama_jenazah', 'nik_jenazah', 'tanggal_wafat', 'jenis_kelamin', 'agama', 'tempat_lahir', 'tanggal_lahir', 'alamat'], 'required', function ($input) {
            return $input->jenis_permohonan === 'makam_baru';
        });

        $validator->sometimes(['makam_id', 'no_makam', 'blok_zona_makam'], 'required', function ($input) {
            return $input->jenis_permohonan === 'perpanjangan';
        });

        $data = $validator->validate();
        $selectedMakam = $data['makam_id'] ? Makam::find($data['makam_id']) : null;

        foreach (['scan_ktp_ahli_waris', 'scan_kk', 'surat_kematian', 'bukti_pembayaran_retribusi'] as $fileKey) {
            if ($request->hasFile($fileKey)) {
                $data[$fileKey] = $request->file($fileKey)->store('permohonan/' . $fileKey, 'public');
            }
        }

        $permohonan->fill(array_merge($data, [
            'makam_id' => $selectedMakam?->id ?? null,
            'kode_makam' => $selectedMakam?->kode_makam ?? $data['kode_makam'] ?? null,
            'blok' => $selectedMakam?->blok ?? $data['blok'] ?? null,
            'zona' => $selectedMakam?->zona ?? $data['zona'] ?? null,
            'nomor_makam' => $selectedMakam?->nomor ?? $data['nomor_makam'] ?? null,
            'keterangan' => $data['keterangan'] ?? $selectedMakam?->keterangan ?? null,
            'no_makam' => $selectedMakam?->nomor ?? $data['no_makam'] ?? null,
            'blok_zona_makam' => $selectedMakam ? trim(implode(' / ', array_filter([$selectedMakam->blok, $selectedMakam->zona])), ' /') : ($data['blok_zona_makam'] ?? null),
            'tahun_pemakaman' => $data['tahun_pemakaman'] ?? null,
        ]));
        $permohonan->save();
        $permohonan->syncLinkedJenazahData();

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
                $permohonan->approved_at = now();
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

                if ($permohonan->hasCompleteJenazahData() && ! $permohonan->jenazah_id) {
                    \Log::info('Memulai create jenazah dari permohonan', ['permohonan_id' => $permohonan->id]);
                    $permohonan->persistJenazahRecord();
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
}
