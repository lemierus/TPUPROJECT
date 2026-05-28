<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jenazah;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PermohonanController extends Controller
{
    /**
     * Menampilkan semua data permohonan
     */
    public function index()
    {
        $permohonans = $this->accessiblePermohonans()
            ->latest()
            ->get();

        $permohonans->each(function (Permohonan $permohonan) {
            $permohonan->syncLinkedJenazahData();
        });

        return view('pages.master.permohonan', compact('permohonans'));
    }

    public function create()
    {
        return view('pages.master.form_permohonan', [
            'permohonan' => new Permohonan(),
            'users' => User::where('role', User::ROLE_USER)->orderBy('name')->get(),
            'jenazah' => Jenazah::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Permohonan::create($this->validatedData($request));

        return redirect()->route($this->routePrefix().'.master.permohonan')
            ->with('success', 'Permohonan berhasil ditambahkan');
    }

    public function edit(Permohonan $permohonan)
    {
        $permohonan = $this->findAccessiblePermohonanOrFail($permohonan->id);
        $permohonan->syncLinkedJenazahData();

        return view('pages.master.form_permohonan', [
            'permohonan' => $permohonan,
            'users' => User::where('role', User::ROLE_USER)->orderBy('name')->get(),
            'jenazah' => Jenazah::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Permohonan $permohonan)
    {
        $permohonan = $this->findAccessiblePermohonanOrFail($permohonan->id);
        $permohonan->update($this->validatedData($request));
        $permohonan->syncLinkedJenazahData();

        return redirect()->route($this->routePrefix().'.master.permohonan')
            ->with('success', 'Permohonan berhasil diperbarui');
    }

    public function destroy(Permohonan $permohonan)
    {
        $permohonan = $this->findAccessiblePermohonanOrFail($permohonan->id);
        $permohonan->delete();

        return redirect()->route($this->routePrefix().'.master.permohonan')
            ->with('success', 'Permohonan berhasil dihapus');
    }

    /**
     * Update status permohonan (setujui / tolak)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:disetujui,ditolak'
        ]);

        $permohonan = $this->findAccessiblePermohonanOrFail($id);

        DB::transaction(function () use ($request, $permohonan) {
            $permohonan->status = $request->status;
            $permohonan->save();

            if ($request->status === 'disetujui' && $permohonan->jenis_permohonan === 'makam_baru' && ! $permohonan->jenazah_id) {
                $this->createJenazahFromPermohonan($permohonan);
            }
        });

        return redirect()->route($this->routePrefix().'.master.permohonan')
            ->with('success', 'Status permohonan berhasil diperbarui');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'tpu' => ['required', Rule::in(['TPU Tunggul Hitam', 'TPU Bungus Teluk Kabung', 'TPU Air Dingin'])],
            'jenazah_id' => ['required', 'exists:jenazah,id'],
            'status' => ['required', 'in:menunggu,disetujui,ditolak'],
            'catatan' => ['nullable', 'string'],
        ]);

        if (auth()->user()?->isPetugas()) {
            $data['tpu'] = auth()->user()->tpu;
        }

        return $data;
    }

    private function routePrefix(): string
    {
        return request()->routeIs('petugas.*') ? 'petugas' : 'admin';
    }

    private function accessiblePermohonans()
    {
        return Permohonan::with(['user', 'jenazah', 'makam'])
            ->when(auth()->user()?->isPetugas(), function ($query) {
                $query->where('tpu', auth()->user()->tpu);
            });
    }

    private function findAccessiblePermohonanOrFail(int $id): Permohonan
    {
        return $this->accessiblePermohonans()->findOrFail($id);
    }

    private function createJenazahFromPermohonan(Permohonan $permohonan): void
    {
        if (Jenazah::where('nik', $permohonan->nik_jenazah)->exists()) {
            throw ValidationException::withMessages([
                'status' => 'Permohonan tidak bisa disetujui karena NIK jenazah sudah terdaftar di data jenazah.',
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
