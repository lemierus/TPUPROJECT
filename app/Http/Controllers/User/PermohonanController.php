<?php

namespace App\Http\Controllers\User;

use App\Concerns\WhatsAppNotifiable;
use App\Http\Controllers\Controller;
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
        $selectedSourcePermohonanId = request('source_permohonan_id');
        $sourcePermohonanId = request('source_permohonan_id');
        $renewalSource = null;

        abort_unless(in_array($tpu, User::tpuOptions(), true), 404);

        $assignedPetugas = User::where('role', User::ROLE_PETUGAS)
            ->where('tpu', $tpu)
            ->first();

        if ($selectedJenis === Permohonan::JENIS_PERPANJANGAN && $sourcePermohonanId) {
            $renewalSource = Permohonan::with(['jenazah.makam', 'makam'])
                ->where('user_id', auth()->id())
                ->where('tpu', $tpu)
                ->where('status', Permohonan::STATUS_DISETUJUI)
                ->where('jenis_permohonan', Permohonan::JENIS_MAKAM_BARU)
                ->find($sourcePermohonanId);

            if ($renewalSource) {
                $selectedRenewalJenazahId = $renewalSource->jenazah_id;
            }
        }

        $perpanjanganJenazahs = $renewalSource
            ? collect([$renewalSource])
            : $this->eligibleRenewalJenazahs($tpu);

        return view('user.permohonan.create', [
            'tpu' => $tpu,
            'selectedJenis' => $selectedJenis,
            'selectedRenewalJenazahId' => $selectedRenewalJenazahId,
            'selectedSourcePermohonanId' => $selectedSourcePermohonanId,
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

            $renewalSource = null;
            $selectedMakam = null;

            if ($data['jenis_permohonan'] === Permohonan::JENIS_PERPANJANGAN) {
                $renewalSource = ! empty($data['source_permohonan_id'] ?? null)
                    ? $this->resolveRenewalSourceById((int) $data['source_permohonan_id'], $data['tpu'])
                    : $this->resolveRenewalSource($data['jenazah_id'] ?? null, $data['tpu']);

                if (! $renewalSource) {
                    throw ValidationException::withMessages([
                        'jenazah_id' => 'Pilih jenazah yang sudah disetujui dan memiliki makam aktif.',
                    ]);
                }

                $selectedMakam = $renewalSource->jenazah?->makam ?? $renewalSource->makam;
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
                'jenazah_id' => $renewalSource?->jenazah_id ?? null,
                'nama_jenazah' => $renewalSource?->nama_jenazah ?? ($data['nama_jenazah'] ?? null),
                'nik_jenazah' => $renewalSource?->nik_jenazah ?? ($data['nik_jenazah'] ?? null),
                'tempat_lahir' => $renewalSource?->tempat_lahir ?? ($data['tempat_lahir'] ?? null),
                'alamat' => $renewalSource?->alamat ?? ($data['alamat'] ?? null),
                'tanggal_lahir' => $renewalSource?->tanggal_lahir ?? ($data['tanggal_lahir'] ?? null),
                'tanggal_wafat' => $renewalSource?->tanggal_wafat ?? ($data['tanggal_wafat'] ?? null),
                'jenis_kelamin' => $renewalSource?->jenis_kelamin ?? ($data['jenis_kelamin'] ?? null),
                'agama' => $renewalSource?->agama ?? ($data['agama'] ?? null),
                'nama_ahli_waris' => $data['nama_ahli_waris'],
                'no_hp_ahli_waris' => $data['no_hp_ahli_waris'],
                'hubungan_keluarga' => $data['hubungan_keluarga'],
                'scan_ktp_ahli_waris' => $this->storeUploadedFile($request, 'scan_ktp_ahli_waris', 'permohonan/ktp'),
                'scan_kk' => $this->storeUploadedFile($request, 'scan_kk', 'permohonan/kk'),
                'surat_kematian' => $this->storeUploadedFile($request, 'surat_kematian', 'permohonan/surat-kematian'),
                'makam_id' => $selectedMakam?->id ?? ($data['makam_id'] ?? null),
                'tahun_pemakaman' => $renewalSource?->tahun_pemakaman ?? ($data['tahun_pemakaman'] ?? null),
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
            'source_permohonan_id' => ['nullable', 'exists:permohonans,id'],
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

            $renewalSource = null;
            $selectedMakam = null;

            if ($data['jenis_permohonan'] === Permohonan::JENIS_PERPANJANGAN) {
                $renewalSource = ! empty($data['source_permohonan_id'] ?? null)
                    ? $this->resolveRenewalSourceById((int) $data['source_permohonan_id'], $permohonan->tpu)
                    : $this->resolveRenewalSource($data['jenazah_id'] ?? $permohonan->jenazah_id, $permohonan->tpu);
                if (! $renewalSource) {
                    throw ValidationException::withMessages([
                        'jenazah_id' => 'Pilih jenazah yang sudah disetujui dan memiliki makam aktif.',
                    ]);
                }
                $selectedMakam = $renewalSource->jenazah?->makam ?? $renewalSource->makam;
            } else {
                $selectedMakam = ($data['makam_id'] ?? null) ? Makam::find($data['makam_id']) : null;
            }

            $permohonan->fill([
                'jenis_permohonan' => $data['jenis_permohonan'],
                'jenazah_id' => $renewalSource?->jenazah_id ?? $data['jenazah_id'] ?? null,
                'nama_jenazah' => $renewalSource?->nama_jenazah ?? $data['nama_jenazah'] ?? null,
                'nik_jenazah' => $renewalSource?->nik_jenazah ?? $data['nik_jenazah'] ?? null,
                'tempat_lahir' => $renewalSource?->tempat_lahir ?? $data['tempat_lahir'] ?? null,
                'tanggal_lahir' => $renewalSource?->tanggal_lahir ?? $data['tanggal_lahir'] ?? null,
                'tanggal_wafat' => $renewalSource?->tanggal_wafat ?? $data['tanggal_wafat'] ?? null,
                'jenis_kelamin' => $renewalSource?->jenis_kelamin ?? $data['jenis_kelamin'] ?? null,
                'agama' => $renewalSource?->agama ?? $data['agama'] ?? null,
                'nama_ahli_waris' => $data['nama_ahli_waris'],
                'no_hp_ahli_waris' => $data['no_hp_ahli_waris'],
                'hubungan_keluarga' => $data['hubungan_keluarga'],
                'alamat' => $data['alamat'] ?? null,
                'makam_id' => $selectedMakam?->id ?? ($data['makam_id'] ?? null),
                'tahun_pemakaman' => $renewalSource?->tahun_pemakaman ?? $data['tahun_pemakaman'] ?? null,
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
            'source_permohonan_id' => ['nullable', 'exists:permohonans,id'],
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

    private function eligibleRenewalJenazahs(string $tpu)
    {
        $query = Permohonan::with(['jenazah.makam', 'makam'])
            ->where('user_id', auth()->id())
            ->where('tpu', $tpu)
            ->where('status', Permohonan::STATUS_DISETUJUI)
            ->where('jenis_permohonan', Permohonan::JENIS_MAKAM_BARU)
            ->whereNotNull('jenazah_id');

        if (Schema::hasColumn('permohonans', 'approved_at')) {
            $query->orderByDesc('approved_at');
        } else {
            $query->orderByDesc('updated_at');
        }

        return $query->get()
            ->filter(fn (Permohonan $permohonan) => $permohonan->jenazah && $permohonan->makam)
            ->values();
    }

    private function resolveRenewalSource(?int $jenazahId, string $tpu): ?Permohonan
    {
        if (! $jenazahId) {
            return null;
        }

        return Permohonan::with(['jenazah.makam', 'makam'])
            ->where('user_id', auth()->id())
            ->where('tpu', $tpu)
            ->where('status', Permohonan::STATUS_DISETUJUI)
            ->where('jenis_permohonan', Permohonan::JENIS_MAKAM_BARU)
            ->where('jenazah_id', $jenazahId)
            ->first();
    }

    private function resolveRenewalSourceById(int $permohonanId, string $tpu): ?Permohonan
    {
        return Permohonan::with(['jenazah.makam', 'makam'])
            ->where('id', $permohonanId)
            ->where('user_id', auth()->id())
            ->where('tpu', $tpu)
            ->where('status', Permohonan::STATUS_DISETUJUI)
            ->where('jenis_permohonan', Permohonan::JENIS_MAKAM_BARU)
            ->first();
    }

    private function ensureOwnedByCurrentUser(Permohonan $permohonan): void
    {
        abort_unless($permohonan->user_id === auth()->id(), 403);
    }
}
