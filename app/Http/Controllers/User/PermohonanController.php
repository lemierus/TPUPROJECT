<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Makam;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class PermohonanController extends Controller
{
    public function create()
    {
        $tpu = request('tpu');
        $selectedJenis = request('jenis_permohonan', 'makam_baru');
        $selectedRenewalJenazahId = request('jenazah_id');
        $selectedSourcePermohonanId = request('source_permohonan_id');
        $sourcePermohonanId = request('source_permohonan_id');
        $renewalSource = null;

        abort_unless(in_array($tpu, User::tpuOptions(), true), 404);

        $assignedPetugas = User::where('role', User::ROLE_PETUGAS)
            ->where('tpu', $tpu)
            ->first();

        if ($selectedJenis === 'perpanjangan' && $sourcePermohonanId) {
            $renewalSource = Permohonan::with(['jenazah.makam', 'makam'])
                ->where('user_id', auth()->id())
                ->where('tpu', $tpu)
                ->where('status', 'disetujui')
                ->where('jenis_permohonan', 'makam_baru')
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
        $data = $request->validate([
            'tpu' => ['required', Rule::in(User::tpuOptions())],
            'jenis_permohonan' => ['required', Rule::in(['makam_baru', 'perpanjangan'])],
            'jenazah_id' => ['required_if:jenis_permohonan,perpanjangan', 'nullable', 'exists:jenazah,id'],
            'source_permohonan_id' => ['nullable', 'exists:permohonans,id'],
            'nama_jenazah' => ['nullable', 'string', 'max:255'],
            'nik_jenazah' => ['nullable', 'string', 'max:255'],
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
            'catatan' => ['nullable', 'string'],
            'scan_ktp_ahli_waris' => [
                $request->jenis_permohonan !== 'perpanjangan' ? 'required' : 'nullable',
                'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'
            ],
            'scan_kk' => [
                $request->jenis_permohonan !== 'perpanjangan' ? 'required' : 'nullable',
                'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'
            ],
            'surat_kematian' => [
                $request->jenis_permohonan !== 'perpanjangan' ? 'required' : 'nullable',
                'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'
            ],
        ]);

        $data['nama_jenazah'] = $data['nama_jenazah'] ?? null;
        $data['nik_jenazah'] = $data['nik_jenazah'] ?? null;

        DB::transaction(function () use ($data, $request) {
            $ktpPath = $request->hasFile('scan_ktp_ahli_waris')
                ? $request->file('scan_ktp_ahli_waris')->store('permohonan/ktp', 'public')
                : null;
            $kkPath = $request->hasFile('scan_kk')
                ? $request->file('scan_kk')->store('permohonan/kk', 'public')
                : null;
            $suratPath = $request->hasFile('surat_kematian')
                ? $request->file('surat_kematian')->store('permohonan/surat-kematian', 'public')
                : null;
            $petugas = User::where('role', User::ROLE_PETUGAS)
                ->where('tpu', $data['tpu'])
                ->first();

            $renewalSource = null;
            $selectedMakam = null;

            if ($data['jenis_permohonan'] === 'perpanjangan') {
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

            $permohonan = Permohonan::create([
                'user_id' => auth()->id(),
                'tpu' => $data['tpu'],
                'petugas_id' => $petugas?->id,
                'jenis_permohonan' => $data['jenis_permohonan'],
                'nama_pemohon' => auth()->user()->name,
                'jenazah_id' => $renewalSource?->jenazah_id ?? null,
                'nama_jenazah' => $renewalSource?->nama_jenazah ?? $data['nama_jenazah'],
                'nik_jenazah' => $renewalSource?->nik_jenazah ?? $data['nik_jenazah'],
                'tempat_lahir' => $renewalSource?->tempat_lahir ?? $data['tempat_lahir'] ?? null,
                'alamat' => $renewalSource?->alamat ?? $data['alamat'] ?? null,
                'tanggal_lahir' => $renewalSource?->tanggal_lahir ?? $data['tanggal_lahir'] ?? null,
                'tanggal_wafat' => $renewalSource?->tanggal_wafat ?? $data['tanggal_wafat'] ?? null,
                'jenis_kelamin' => $renewalSource?->jenis_kelamin ?? $data['jenis_kelamin'] ?? null,
                'agama' => $renewalSource?->agama ?? $data['agama'] ?? null,
                'nama_ahli_waris' => $data['nama_ahli_waris'],
                'no_hp_ahli_waris' => $data['no_hp_ahli_waris'],
                'hubungan_keluarga' => $data['hubungan_keluarga'],
                'scan_ktp_ahli_waris' => $data['jenis_permohonan'] === 'perpanjangan' ? null : $ktpPath,
                'scan_kk' => $data['jenis_permohonan'] === 'perpanjangan' ? null : $kkPath,
                'surat_kematian' => $data['jenis_permohonan'] === 'perpanjangan' ? null : $suratPath,
                'makam_id' => $selectedMakam?->id ?? ($data['makam_id'] ?? null),
                'tahun_pemakaman' => $renewalSource?->tahun_pemakaman ?? $data['tahun_pemakaman'] ?? null,
                'status' => 'menunggu',
                'catatan' => $data['catatan'] ?? null,
            ]);

            $permohonan->syncLinkedJenazahData();
        });

        return redirect()->route('user.dashboard')
            ->with('success', 'Permohonan berhasil dikirim dan menunggu verifikasi.');
    }

    public function edit(Permohonan $permohonan)
    {
        abort_unless($permohonan->user_id === auth()->id(), 403);

        return view('user.permohonan.edit', [
            'permohonan' => $permohonan,
            'makams' => Makam::where('tpu', $permohonan->tpu)->orderBy('kode_makam')->get(),
            'assignedPetugas' => $permohonan->assignedPetugas(),
            'perpanjanganJenazahs' => $this->eligibleRenewalJenazahs($permohonan->tpu),
        ]);
    }

    public function summary(Permohonan $permohonan)
    {
        abort_unless($permohonan->user_id === auth()->id(), 403);
        abort_unless($permohonan->jenis_permohonan === 'makam_baru', 404);

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

    public function update(Request $request, Permohonan $permohonan)
    {
        abort_unless($permohonan->user_id === auth()->id(), 403);

        if ($permohonan->status !== 'menunggu' && $permohonan->status !== 'pending') {
            return redirect()->back()->with('error', 'Permohonan tidak dapat diedit setelah diproses.');
        }

        $data = $request->validate([
            'jenis_permohonan' => ['required', Rule::in(['makam_baru', 'perpanjangan', 'pemindahan_makam', 'renovasi_makam'])],
            'jenazah_id' => ['required_if:jenis_permohonan,perpanjangan', 'nullable', 'exists:jenazah,id'],
            'source_permohonan_id' => ['nullable', 'exists:permohonans,id'],
            'nama_jenazah' => ['nullable', 'string', 'max:255'],
            'nik_jenazah' => ['nullable', 'string', 'max:255'],
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
            'catatan' => ['nullable', 'string'],
            'scan_ktp_ahli_waris' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'scan_kk' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'surat_kematian' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        DB::transaction(function () use ($request, $permohonan, $data) {
            foreach (['scan_ktp_ahli_waris', 'scan_kk', 'surat_kematian'] as $fileKey) {
                if ($request->hasFile($fileKey)) {
                    $path = $request->file($fileKey)->store('permohonan/' . $fileKey, 'public');
                    $permohonan->{$fileKey} = $path;
                }
            }

            $renewalSource = null;
            $selectedMakam = null;

            if ($data['jenis_permohonan'] === 'perpanjangan') {
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

    private function eligibleRenewalJenazahs(string $tpu)
    {
        $query = Permohonan::with(['jenazah.makam', 'makam'])
            ->where('user_id', auth()->id())
            ->where('tpu', $tpu)
            ->where('status', 'disetujui')
            ->where('jenis_permohonan', 'makam_baru')
            ->whereNotNull('jenazah_id');

        if (Schema::hasColumn('permohonans', 'approved_at')) {
            $query->orderByDesc('approved_at');
        } else {
            $query->orderByDesc('updated_at');
        }

        return $query->get()
            ->filter(function (Permohonan $permohonan) {
                return $permohonan->jenazah && $permohonan->makam;
            })
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
            ->where('status', 'disetujui')
            ->where('jenis_permohonan', 'makam_baru')
            ->where('jenazah_id', $jenazahId)
            ->first();
    }

    private function resolveRenewalSourceById(int $permohonanId, string $tpu): ?Permohonan
    {
        return Permohonan::with(['jenazah.makam', 'makam'])
            ->where('id', $permohonanId)
            ->where('user_id', auth()->id())
            ->where('tpu', $tpu)
            ->where('status', 'disetujui')
            ->where('jenis_permohonan', 'makam_baru')
            ->first();
    }
}
