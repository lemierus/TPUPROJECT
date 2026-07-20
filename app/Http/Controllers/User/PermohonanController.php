<?php

namespace App\Http\Controllers\User;

use App\Concerns\WhatsAppNotifiable;
use App\Http\Controllers\Controller;
use App\Models\Jenazah;
use App\Models\Makam;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PermohonanController extends Controller
{
    use WhatsAppNotifiable;

    public function create()
    {
        $tpu = request('tpu');
        $selectedJenis = request('jenis_permohonan', Permohonan::JENIS_MAKAM_BARU);
        $selectedRenewalJenazahId = request('jenazah_id');
        $renewalSource = null;

        abort_unless(in_array($tpu, User::tpuOptions(), true), 404);

        $assignedPetugas = User::where('role', User::ROLE_PETUGAS)
            ->where('tpu', $tpu)
            ->first();

        if ($selectedJenis === Permohonan::JENIS_PERPANJANGAN && $selectedRenewalJenazahId) {
            $renewalSource = $this->resolveRenewalJenazah((int) $selectedRenewalJenazahId, $tpu);

            if ($renewalSource) {
                $selectedRenewalJenazahId = $renewalSource->id;
            }
        }

        // Jika user datang lewat tombol "perpanjang" untuk jenazah tertentu,
        // dropdown dikunci hanya ke jenazah itu. Jika tidak, tampilkan semua
        // jenazah (termasuk rekan tumpang sari di makam yang sama) yang
        // berhak diperpanjang oleh user ini.
        $perpanjanganJenazahs = $renewalSource
            ? collect([$renewalSource])
            : $this->eligibleRenewalJenazahs($tpu);

        return view('user.permohonan.create', [
            'tpu' => $tpu,
            'selectedJenis' => $selectedJenis,
            'selectedRenewalJenazahId' => $selectedRenewalJenazahId,
            'renewalSource' => $renewalSource,
            'makams' => Makam::where('tpu', $tpu)->orderBy('kode_makam')->get(),
            'assignedPetugas' => $assignedPetugas,
            'perpanjanganJenazahs' => $perpanjanganJenazahs,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateStoreRequest($request);

        $permohonan = DB::transaction(function () use ($data, $request) {
            $petugas = User::where('role', User::ROLE_PETUGAS)
                ->where('tpu', $data['tpu'])
                ->first();

            $renewalJenazah = null;
            $selectedMakam = null;

            if ($data['jenis_permohonan'] === Permohonan::JENIS_PERPANJANGAN) {
                $renewalJenazah = $this->resolveRenewalJenazah($data['jenazah_id'] ?? null, $data['tpu']);

                if (! $renewalJenazah) {
                    throw ValidationException::withMessages([
                        'jenazah_id' => 'Pilih jenazah yang sudah disetujui dan memiliki makam aktif.',
                    ]);
                }

                $selectedMakam = $renewalJenazah->makam;
            } else {
                $selectedMakam = ($data['makam_id'] ?? null) ? Makam::find($data['makam_id']) : null;
            }

            $isDarurat = $data['jenis_permohonan'] === Permohonan::JENIS_DARURAT;

            $permohonan = Permohonan::create([
                'user_id' => auth()->id(),
                'tpu' => $data['tpu'],
                'petugas_id' => $petugas?->id,
                'jenis_permohonan' => $data['jenis_permohonan'],
                'nama_pemohon' => auth()->user()->name,
                'jenazah_id' => $renewalJenazah?->id ?? null,
                'nama_jenazah' => $renewalJenazah?->nama ?? ($data['nama_jenazah'] ?? null),
                'nik_jenazah' => $renewalJenazah?->nik ?? ($data['nik_jenazah'] ?? null),
                'tempat_lahir' => $renewalJenazah?->tempat_lahir ?? ($data['tempat_lahir'] ?? null),
                'alamat' => $renewalJenazah?->alamat ?? ($data['alamat'] ?? null),
                'tanggal_lahir' => $renewalJenazah?->tanggal_lahir ?? ($data['tanggal_lahir'] ?? null),
                'tanggal_wafat' => $renewalJenazah?->tanggal_wafat ?? ($data['tanggal_wafat'] ?? null),
                'jenis_kelamin' => $renewalJenazah?->jenis_kelamin ?? ($data['jenis_kelamin'] ?? null),
                'agama' => $renewalJenazah?->agama ?? ($data['agama'] ?? null),
                'nama_ahli_waris' => $data['nama_ahli_waris'],
                'no_hp_ahli_waris' => $data['no_hp_ahli_waris'],
                'hubungan_keluarga' => $data['hubungan_keluarga'],
                'scan_ktp_ahli_waris' => $this->storeUploadedFile($request, 'scan_ktp_ahli_waris', 'permohonan/ktp'),
                'scan_kk' => $this->storeUploadedFile($request, 'scan_kk', 'permohonan/kk'),
                'surat_kematian' => $this->storeUploadedFile($request, 'surat_kematian', 'permohonan/surat-kematian'),
                'makam_id' => $selectedMakam?->id ?? ($data['makam_id'] ?? null),
                'tahun_pemakaman' => $data['tahun_pemakaman'] ?? null,
                'status' => $isDarurat ? Permohonan::STATUS_MENUNGGU_KONFIRMASI : Permohonan::STATUS_MENUNGGU,
                'catatan' => $data['catatan'] ?? null,
            ]);

            $permohonan->syncLinkedJenazahData();

            return $permohonan->fresh(['petugas']);
        });

        if ($permohonan->isDarurat()) {
            return redirect()->route('user.permohonan.darurat-sukses', $permohonan)
                ->with('success', 'Permohonan darurat berhasil dikirim.');
        }

        return redirect()->route('user.dashboard')
            ->with('success', 'Permohonan berhasil dikirim dan menunggu verifikasi.');
    }

    public function daruratSukses(Permohonan $permohonan)
    {
        $this->ensureOwnedByCurrentUser($permohonan);
        abort_unless($permohonan->isDarurat(), 404);

        return view('user.permohonan.darurat-sukses', [
            'permohonan' => $permohonan,
            'waPetugasUrl' => $this->notifyDaruratPermohonan($permohonan),
        ]);
    }

    public function edit(Permohonan $permohonan)
    {
        $this->ensureOwnedByCurrentUser($permohonan);

        return view('user.permohonan.edit', [
            'permohonan' => $permohonan,
            'makams' => Makam::where('tpu', $permohonan->tpu)->orderBy('kode_makam')->get(),
            'assignedPetugas' => $permohonan->assignedPetugas(),
            'perpanjanganJenazahs' => $this->eligibleRenewalJenazahs($permohonan->tpu),
        ]);
    }

    public function summary(Permohonan $permohonan)
    {
        $this->ensureOwnedByCurrentUser($permohonan);
        abort_unless(in_array($permohonan->jenis_permohonan, [Permohonan::JENIS_MAKAM_BARU, Permohonan::JENIS_DARURAT], true), 404);

        $permohonan->load(['jenazah.makam', 'makam', 'petugas']);
        $permohonan->syncLinkedJenazahData();
        $permohonan->refresh()->load(['jenazah.makam', 'makam', 'petugas']);

        return view('user.permohonan.summary', [
            'permohonan' => $permohonan,
            'jenazah' => $permohonan->jenazah,
            'makam' => $permohonan->makam,
            'ahliWaris' => $permohonan,
        ]);
    }

    public function lengkapiDokumen(Permohonan $permohonan)
    {
        $this->ensureOwnedByCurrentUser($permohonan);
        abort_unless($permohonan->needsDocumentCompletion(), 403);

        return view('user.permohonan.lengkapi-dokumen', [
            'permohonan' => $permohonan,
        ]);
    }

    public function updateDokumen(Request $request, Permohonan $permohonan)
    {
        $this->ensureOwnedByCurrentUser($permohonan);
        abort_unless($permohonan->needsDocumentCompletion(), 403);

        $data = $request->validate([
            'nik_jenazah' => ['required', 'digits:16'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'alamat' => ['required', 'string'],
            'scan_ktp_ahli_waris' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'scan_kk' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'surat_kematian' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'catatan' => ['required', 'string'],
        ], [
            'nik_jenazah.required' => 'NIK jenazah wajib diisi.',
            'nik_jenazah.digits' => 'NIK jenazah harus terdiri dari 16 digit.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'alamat.required' => 'Alamat wajib diisi.',
            'scan_ktp_ahli_waris.required' => 'Scan KTP ahli waris wajib diunggah.',
            'scan_kk.required' => 'Scan KK wajib diunggah.',
            'surat_kematian.required' => 'Surat kematian wajib diunggah.',
        ]);

        DB::transaction(function () use ($request, $permohonan, $data) {
            $permohonan->fill([
                'nik_jenazah' => $data['nik_jenazah'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'alamat' => $data['alamat'],
                'catatan' => $data['catatan'] ?? $permohonan->catatan,
                'scan_ktp_ahli_waris' => $this->replaceUploadedFile($request, $permohonan->scan_ktp_ahli_waris, 'scan_ktp_ahli_waris', 'permohonan/ktp'),
                'scan_kk' => $this->replaceUploadedFile($request, $permohonan->scan_kk, 'scan_kk', 'permohonan/kk'),
                'surat_kematian' => $this->replaceUploadedFile($request, $permohonan->surat_kematian, 'surat_kematian', 'permohonan/surat-kematian'),
                'status' => Permohonan::STATUS_MENUNGGU_VERIFIKASI_DOKUMEN,
                'catatan_revisi' => null,
            ]);
            $permohonan->save();
            $permohonan->syncLinkedJenazahData();

            // Setelah ahli waris melengkapi dokumen, sinkronkan juga ke record
            // jenazah yang sudah dibuat saat pemakaman darurat agar detail
            // permohonan petugas selalu menampilkan data terbaru yang sama.
            if ($permohonan->jenazah_id || filled($permohonan->nik_jenazah)) {
                $permohonan->persistJenazahRecord();
            }
        });

        return redirect()->route('user.dashboard')
            ->with('success', 'Dokumen berhasil dilengkapi dan menunggu verifikasi petugas.');
    }

    public function update(Request $request, Permohonan $permohonan)
    {
        $this->ensureOwnedByCurrentUser($permohonan);

        if (! in_array($permohonan->status, [Permohonan::STATUS_MENUNGGU, Permohonan::STATUS_PENDING], true)) {
            return redirect()->back()->with('error', 'Permohonan tidak dapat diedit setelah diproses.');
        }

        $data = $request->validate([
            'jenis_permohonan' => ['required', Rule::in([Permohonan::JENIS_MAKAM_BARU, Permohonan::JENIS_PERPANJANGAN])],
            'jenazah_id' => ['required_if:jenis_permohonan,perpanjangan', 'nullable', 'exists:jenazah,id'],
            'nama_jenazah' => ['nullable', 'string', 'max:255'],
            'nik_jenazah' => ['nullable', 'string', 'digits:16'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'tanggal_wafat' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
            'agama' => ['nullable', 'string', 'max:255'],
            'nama_ahli_waris' => ['required', 'string', 'max:255'],
            'no_hp_ahli_waris' => ['required', 'string', 'max:30'],
            'hubungan_keluarga' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'makam_id' => ['nullable', 'exists:makams,id'],
            'tahun_pemakaman' => ['nullable', 'digits:4'],
            'catatan' => ['required', 'string'],
            'scan_ktp_ahli_waris' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'scan_kk' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'surat_kematian' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        DB::transaction(function () use ($request, $permohonan, $data) {
            foreach (['scan_ktp_ahli_waris', 'scan_kk', 'surat_kematian'] as $fileKey) {
                if ($request->hasFile($fileKey)) {
                    $permohonan->{$fileKey} = $request->file($fileKey)->store('permohonan/' . $fileKey, 'public');
                }
            }

            $renewalJenazah = null;
            $selectedMakam = null;

            if ($data['jenis_permohonan'] === Permohonan::JENIS_PERPANJANGAN) {
                $renewalJenazah = $this->resolveRenewalJenazah(
                    $data['jenazah_id'] ?? $permohonan->jenazah_id,
                    $permohonan->tpu
                );

                if (! $renewalJenazah) {
                    throw ValidationException::withMessages([
                        'jenazah_id' => 'Pilih jenazah yang sudah disetujui dan memiliki makam aktif.',
                    ]);
                }
                $selectedMakam = $renewalJenazah->makam;
            } else {
                $selectedMakam = ($data['makam_id'] ?? null) ? Makam::find($data['makam_id']) : null;
            }

            $permohonan->fill([
                'jenis_permohonan' => $data['jenis_permohonan'],
                'jenazah_id' => $renewalJenazah?->id ?? $data['jenazah_id'] ?? null,
                'nama_jenazah' => $renewalJenazah?->nama ?? $data['nama_jenazah'] ?? null,
                'nik_jenazah' => $renewalJenazah?->nik ?? $data['nik_jenazah'] ?? null,
                'tempat_lahir' => $renewalJenazah?->tempat_lahir ?? $data['tempat_lahir'] ?? null,
                'tanggal_lahir' => $renewalJenazah?->tanggal_lahir ?? $data['tanggal_lahir'] ?? null,
                'tanggal_wafat' => $renewalJenazah?->tanggal_wafat ?? $data['tanggal_wafat'] ?? null,
                'jenis_kelamin' => $renewalJenazah?->jenis_kelamin ?? $data['jenis_kelamin'] ?? null,
                'agama' => $renewalJenazah?->agama ?? $data['agama'] ?? null,
                'nama_ahli_waris' => $data['nama_ahli_waris'],
                'no_hp_ahli_waris' => $data['no_hp_ahli_waris'],
                'hubungan_keluarga' => $data['hubungan_keluarga'],
                'alamat' => $data['alamat'] ?? null,
                'makam_id' => $selectedMakam?->id ?? ($data['makam_id'] ?? null),
                'tahun_pemakaman' => $data['tahun_pemakaman'] ?? $permohonan->tahun_pemakaman,
                'catatan' => $data['catatan'] ?? null,
            ]);

            $permohonan->save();
            $permohonan->syncLinkedJenazahData();
        });

        return redirect()->route('user.dashboard')->with('success', 'Permohonan berhasil diperbarui.');
    }

    private function validateStoreRequest(Request $request): array
    {
        return $request->validate([
            'tpu' => ['required', Rule::in(User::tpuOptions())],
            'jenis_permohonan' => ['required', Rule::in([Permohonan::JENIS_MAKAM_BARU, Permohonan::JENIS_PERPANJANGAN, Permohonan::JENIS_DARURAT])],
            'jenazah_id' => ['required_if:jenis_permohonan,' . Permohonan::JENIS_PERPANJANGAN, 'nullable', 'exists:jenazah,id'],
            'nama_jenazah' => [Rule::requiredIf(fn () => in_array($request->jenis_permohonan, [Permohonan::JENIS_MAKAM_BARU, Permohonan::JENIS_DARURAT], true)), 'nullable', 'string', 'max:255'],
            'nik_jenazah' => [Rule::requiredIf(fn () => $request->jenis_permohonan === Permohonan::JENIS_MAKAM_BARU), 'nullable', 'string', 'digits:16'],
            'tempat_lahir' => [Rule::requiredIf(fn () => $request->jenis_permohonan === Permohonan::JENIS_MAKAM_BARU), 'nullable', 'string', 'max:255'],
            'tanggal_lahir' => [Rule::requiredIf(fn () => $request->jenis_permohonan === Permohonan::JENIS_MAKAM_BARU), 'nullable', 'date'],
            'tanggal_wafat' => [Rule::requiredIf(fn () => in_array($request->jenis_permohonan, [Permohonan::JENIS_MAKAM_BARU, Permohonan::JENIS_DARURAT], true)), 'nullable', 'date'],
            'jenis_kelamin' => [Rule::requiredIf(fn () => in_array($request->jenis_permohonan, [Permohonan::JENIS_MAKAM_BARU, Permohonan::JENIS_DARURAT], true)), 'nullable', Rule::in(['Laki-laki', 'Perempuan'])],
            'agama' => [Rule::requiredIf(fn () => in_array($request->jenis_permohonan, [Permohonan::JENIS_MAKAM_BARU, Permohonan::JENIS_DARURAT], true)), 'nullable', 'string', 'max:255'],
            'nama_ahli_waris' => ['required', 'string', 'max:255'],
            'no_hp_ahli_waris' => ['required', 'string', 'max:30'],
            'hubungan_keluarga' => ['required', 'string', 'max:255'],
            'alamat' => [Rule::requiredIf(fn () => $request->jenis_permohonan === Permohonan::JENIS_MAKAM_BARU), 'nullable', 'string'],
            'makam_id' => ['nullable', 'exists:makams,id'],
            'tahun_pemakaman' => ['nullable', 'digits:4'],
            'catatan' => [
                Rule::requiredIf(fn () => in_array(
                    $request->jenis_permohonan,
                    [
                        Permohonan::JENIS_MAKAM_BARU,
                        Permohonan::JENIS_DARURAT,
                    ],
                    true
                )),
                'nullable',
                'string',
            ],
            'scan_ktp_ahli_waris' => [
                Rule::requiredIf(fn () => $request->jenis_permohonan === Permohonan::JENIS_MAKAM_BARU),
                'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048',
            ],
            'scan_kk' => [
                Rule::requiredIf(fn () => $request->jenis_permohonan === Permohonan::JENIS_MAKAM_BARU),
                'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048',
            ],
            'surat_kematian' => [
                Rule::requiredIf(fn () => $request->jenis_permohonan === Permohonan::JENIS_MAKAM_BARU),
                'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048',
            ],
        ], [
            'tpu.required' => 'TPU wajib dipilih.',
            'tpu.in' => 'TPU yang dipilih tidak valid.',
            'jenis_permohonan.required' => 'Jenis permohonan wajib dipilih.',
            'jenis_permohonan.in' => 'Jenis permohonan yang dipilih tidak valid.',
            'jenazah_id.required_if' => 'Silakan pilih jenazah untuk permohonan perpanjangan.',
            'jenazah_id.exists' => 'Data jenazah yang dipilih tidak ditemukan.',
            'nama_jenazah.required' => 'Nama jenazah wajib diisi.',
            'nik_jenazah.digits' => 'NIK jenazah harus terdiri dari tepat 16 digit angka.',
            'tanggal_wafat.required' => 'Tanggal meninggal wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'agama.required' => 'Agama wajib dipilih.',
            'nama_ahli_waris.required' => 'Nama ahli waris wajib diisi.',
            'no_hp_ahli_waris.required' => 'Nomor HP ahli waris wajib diisi.',
            'hubungan_keluarga.required' => 'Hubungan dengan jenazah wajib diisi.',
            'catatan.required' => 'Keterangan/Catatan wajib diisi.',
            'scan_ktp_ahli_waris.required' => 'Scan KTP ahli waris wajib diunggah.',
            'scan_kk.required' => 'Scan Kartu Keluarga wajib diunggah.',
            'surat_kematian.required' => 'Surat kematian wajib diunggah.',
        ]);
    }

    private function storeUploadedFile(Request $request, string $field, string $directory): ?string
    {
        return $request->hasFile($field)
            ? $request->file($field)->store($directory, 'public')
            : null;
    }

    private function replaceUploadedFile(Request $request, ?string $oldPath, string $field, string $directory): ?string
    {
        if (! $request->hasFile($field)) {
            return $oldPath;
        }

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return $request->file($field)->store($directory, 'public');
    }

    /**
     * ID makam yang "dimiliki" user pada TPU tertentu. Dipakai sebagai
     * dasar hak akses untuk fitur perpanjangan.
     *
     * Ada dua jalur permohonan yang bisa menghasilkan kepemilikan makam
     * yang sah, dan keduanya harus dihitung:
     *
     * 1. Jalur reguler: jenis_permohonan = makam_baru, status = disetujui.
     * 2. Jalur darurat: jenis_permohonan = darurat, status = selesai.
     *    Permohonan darurat TIDAK PERNAH berubah jenis_permohonan-nya
     *    menjadi makam_baru, dan status akhirnya adalah 'selesai' (bukan
     *    'disetujui') — ini diset oleh PermohonanController milik petugas
     *    di verifikasiDokumen(). Tanpa baris ini, semua jenazah hasil
     *    pemakaman darurat (termasuk yang tumpang sari) tidak akan pernah
     *    muncul sebagai opsi perpanjangan meskipun administrasinya sudah
     *    lengkap dan diverifikasi penuh oleh petugas.
     */
    private function ownedMakamIds(string $tpu)
    {
        return Permohonan::where('user_id', auth()->id())
            ->where('tpu', $tpu)
            ->whereNotNull('makam_id')
            ->where(function ($query) {
                $query->where(function ($regular) {
                    $regular->where('jenis_permohonan', Permohonan::JENIS_MAKAM_BARU)
                        ->where('status', Permohonan::STATUS_DISETUJUI);
                })->orWhere(function ($darurat) {
                    $darurat->where('jenis_permohonan', Permohonan::JENIS_DARURAT)
                        ->where('status', Permohonan::STATUS_SELESAI);
                });
            })
            ->pluck('makam_id')
            ->unique();
    }

    /**
     * Daftar jenazah yang berhak diperpanjang oleh user untuk TPU tertentu.
     *
     * Satu makam = satu opsi. Untuk makam tumpang sari (berisi lebih dari
     * satu jenazah), perpanjangan TIDAK diwakili oleh seluruh jenazah di
     * makam tersebut — hanya jenazah yang PALING TERAKHIR dimakamkan di
     * makam itu yang tampil sebagai opsi. Urutan "terbaru" ditentukan dari
     * tanggal_wafat paling baru, dengan id terbesar sebagai fallback jika
     * tanggal_wafat sama/kosong (konsisten dengan logika di
     * Makam::syncTenggatSewaFromJenazah() dan Jenazah::latestJenazahInSameMakam()).
     */
    private function eligibleRenewalJenazahs(string $tpu)
    {
        $makamIds = $this->ownedMakamIds($tpu);

        if ($makamIds->isEmpty()) {
            return collect();
        }

        return Jenazah::with('makam')
            ->whereIn('makam_id', $makamIds)
            ->orderByDesc('tanggal_wafat')
            ->orderByDesc('id')
            ->get()
            // Karena koleksi sudah terurut global dari tanggal_wafat/id
            // paling baru, item pertama yang muncul untuk tiap makam_id
            // otomatis adalah jenazah terbaru pada makam tersebut.
            ->groupBy('makam_id')
            ->map(fn ($jenazahsInMakam) => $jenazahsInMakam->first())
            ->values();
    }

    /**
     * Validasi + ambil satu jenazah spesifik untuk keperluan perpanjangan.
     *
     * Jenazah hanya valid jika:
     * 1. Berada di salah satu makam yang dimiliki user pada TPU ini
     *    (lihat ownedMakamIds()), DAN
     * 2. Merupakan jenazah PALING TERAKHIR yang dimakamkan di makam itu.
     *
     * Syarat kedua ini mencegah user memilih/mengirim jenazah_id milik
     * penghuni makam tumpang sari yang lebih lama — perpanjangan makam
     * tumpang sari harus selalu atas nama penghuni terbaru.
     */
    private function resolveRenewalJenazah($jenazahId, string $tpu): ?Jenazah
    {
        if (! $jenazahId) {
            return null;
        }

        $makamIds = $this->ownedMakamIds($tpu);

        if ($makamIds->isEmpty()) {
            return null;
        }

        $jenazah = Jenazah::with('makam')
            ->where('id', $jenazahId)
            ->whereIn('makam_id', $makamIds)
            ->first();

        if (! $jenazah) {
            return null;
        }

        $latestInMakam = Jenazah::where('makam_id', $jenazah->makam_id)
            ->orderByDesc('tanggal_wafat')
            ->orderByDesc('id')
            ->first();

        if ($latestInMakam && $latestInMakam->id !== $jenazah->id) {
            return null;
        }

        return $jenazah;
    }

    private function ensureOwnedByCurrentUser(Permohonan $permohonan): void
    {
        abort_unless($permohonan->user_id === auth()->id(), 403);
    }
}