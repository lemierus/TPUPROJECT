<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Makam;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class PermohonanController extends Controller
{
    public function create()
    {
        $tpu = request('tpu');

        abort_unless(in_array($tpu, ['TPU Tunggul Hitam', 'TPU Bungus Teluk Kabung', 'TPU Air Dingin'], true), 404);

        $assignedPetugas = User::where('role', User::ROLE_PETUGAS)
            ->where('tpu', $tpu)
            ->first();

        return view('user.permohonan.create', [
            'tpu' => $tpu,
            'makams' => Makam::where('tpu', $tpu)->orderBy('kode_makam')->get(),
            'assignedPetugas' => $assignedPetugas,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tpu' => ['required', Rule::in(['TPU Tunggul Hitam', 'TPU Bungus Teluk Kabung', 'TPU Air Dingin'])],
            'jenis_permohonan' => ['required', Rule::in(['makam_baru', 'perpanjangan', 'pemindahan_makam', 'renovasi_makam'])],
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
            'kode_makam' => ['nullable', 'string', 'max:255'],
            'blok' => ['nullable', 'string', 'max:255'],
            'zona' => ['nullable', 'string', 'max:255'],
            'nomor_makam' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'no_makam' => ['nullable', 'string', 'max:255'],
            'blok_zona_makam' => ['nullable', 'string', 'max:255'],
            'tahun_pemakaman' => ['nullable', 'digits:4'],
            'catatan' => ['nullable', 'string'],
            'scan_ktp_ahli_waris' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'scan_kk' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'surat_kematian' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'bukti_pembayaran_retribusi' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $data['nama_jenazah'] = $data['nama_jenazah'] ?? null;
        $data['nik_jenazah'] = $data['nik_jenazah'] ?? null;

        DB::transaction(function () use ($data, $request) {
            $ktpPath = $request->file('scan_ktp_ahli_waris')->store('permohonan/ktp', 'public');
            $kkPath = $request->file('scan_kk')->store('permohonan/kk', 'public');
            $suratPath = $request->file('surat_kematian')->store('permohonan/surat-kematian', 'public');
            $buktiRetribusiPath = $request->file('bukti_pembayaran_retribusi')
                ? $request->file('bukti_pembayaran_retribusi')->store('permohonan/retribusi', 'public')
                : null;

            $petugas = User::where('role', User::ROLE_PETUGAS)
                ->where('tpu', $data['tpu'])
                ->first();

            $selectedMakam = $data['makam_id'] ? Makam::find($data['makam_id']) : null;

            $permohonan = Permohonan::create([
                'user_id' => auth()->id(),
                'tpu' => $data['tpu'],
                'petugas_id' => $petugas?->id,
                'jenis_permohonan' => $data['jenis_permohonan'],
                'nama_pemohon' => auth()->user()->name,
                'nama_jenazah' => $data['nama_jenazah'],
                'nik_jenazah' => $data['nik_jenazah'],
                'tempat_lahir' => $data['tempat_lahir'] ?? null,
                'alamat' => $data['alamat'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'tanggal_wafat' => $data['tanggal_wafat'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'agama' => $data['agama'] ?? null,
                'nama_ahli_waris' => $data['nama_ahli_waris'],
                'no_hp_ahli_waris' => $data['no_hp_ahli_waris'],
                'hubungan_keluarga' => $data['hubungan_keluarga'],
                'scan_ktp_ahli_waris' => $ktpPath,
                'scan_kk' => $kkPath,
                'surat_kematian' => $suratPath,
                'jenazah_id' => null,
                'makam_id' => $selectedMakam?->id ?? null,
                'kode_makam' => $selectedMakam?->kode_makam ?? $data['kode_makam'] ?? null,
                'blok' => $selectedMakam?->blok ?? $data['blok'] ?? null,
                'zona' => $selectedMakam?->zona ?? $data['zona'] ?? null,
                'nomor_makam' => $selectedMakam?->nomor ?? $data['nomor_makam'] ?? null,
                'keterangan' => $data['keterangan'] ?? $selectedMakam?->keterangan ?? null,
                'no_makam' => $selectedMakam?->nomor ?? $data['no_makam'] ?? null,
                'blok_zona_makam' => $selectedMakam ? trim(implode(' / ', array_filter([$selectedMakam->blok, $selectedMakam->zona])), ' /') : ($data['blok_zona_makam'] ?? null),
                'tahun_pemakaman' => $data['tahun_pemakaman'] ?? null,
                'bukti_pembayaran_retribusi' => $buktiRetribusiPath,
                'status' => 'menunggu',
                'catatan' => $data['catatan'] ?? null,
            ]);

            if (in_array($permohonan->jenis_permohonan, ['makam_baru', 'pemindahan_makam', 'renovasi_makam']) && ! $permohonan->jenazah_id) {
                $permohonan->persistJenazahRecord();
            } else {
                $permohonan->syncLinkedJenazahData();
            }
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
            'kode_makam' => ['nullable', 'string', 'max:255'],
            'blok' => ['nullable', 'string', 'max:255'],
            'zona' => ['nullable', 'string', 'max:255'],
            'nomor_makam' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'no_makam' => ['nullable', 'string', 'max:255'],
            'blok_zona_makam' => ['nullable', 'string', 'max:255'],
            'tahun_pemakaman' => ['nullable', 'digits:4'],
            'catatan' => ['nullable', 'string'],
            'scan_ktp_ahli_waris' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'scan_kk' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'surat_kematian' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'bukti_pembayaran_retribusi' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        DB::transaction(function () use ($request, $permohonan, $data) {
            foreach (['scan_ktp_ahli_waris', 'scan_kk', 'surat_kematian', 'bukti_pembayaran_retribusi'] as $fileKey) {
                if ($request->hasFile($fileKey)) {
                    $path = $request->file($fileKey)->store('permohonan/' . $fileKey, 'public');
                    $permohonan->{$fileKey} = $path;
                }
            }

            $selectedMakam = $data['makam_id'] ? Makam::find($data['makam_id']) : null;

            $permohonan->fill([
                'jenis_permohonan' => $data['jenis_permohonan'],
                'nama_jenazah' => $data['nama_jenazah'] ?? null,
                'nik_jenazah' => $data['nik_jenazah'] ?? null,
                'tempat_lahir' => $data['tempat_lahir'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'tanggal_wafat' => $data['tanggal_wafat'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'agama' => $data['agama'] ?? null,
                'nama_ahli_waris' => $data['nama_ahli_waris'],
                'no_hp_ahli_waris' => $data['no_hp_ahli_waris'],
                'hubungan_keluarga' => $data['hubungan_keluarga'],
                'alamat' => $data['alamat'] ?? null,
                'makam_id' => $selectedMakam?->id ?? null,
                'kode_makam' => $selectedMakam?->kode_makam ?? $data['kode_makam'] ?? null,
                'blok' => $selectedMakam?->blok ?? $data['blok'] ?? null,
                'zona' => $selectedMakam?->zona ?? $data['zona'] ?? null,
                'nomor_makam' => $selectedMakam?->nomor ?? $data['nomor_makam'] ?? null,
                'keterangan' => $data['keterangan'] ?? $selectedMakam?->keterangan ?? null,
                'no_makam' => $selectedMakam?->nomor ?? $data['no_makam'] ?? null,
                'blok_zona_makam' => $selectedMakam ? trim(implode(' / ', array_filter([$selectedMakam->blok, $selectedMakam->zona])), ' /') : ($data['blok_zona_makam'] ?? null),
                'tahun_pemakaman' => $data['tahun_pemakaman'] ?? null,
                'catatan' => $data['catatan'] ?? null,
            ]);

            $permohonan->save();
            $permohonan->syncLinkedJenazahData();
        });

        return redirect()->route('user.dashboard')->with('success', 'Permohonan berhasil diperbarui.');
    }
}
