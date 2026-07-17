<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jenazah;
use App\Models\Makam;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;

class DataJenazahController extends Controller
{
    // ✅ TAMPILKAN DATA + SEARCH
    public function index(Request $request)
    {
        $search = $request->search;
        $filter = $request->filter ?? 'harian';
        $selectedTpu = $request->tpu;
        $tpuOptions = User::tpuOptions();

        if (auth()->user()?->isPetugas() || auth()->user()?->isKepala()) {
            $this->syncApprovedPermohonanJenazah();
        }

        $jenazahQuery = $this->accessibleJenazah()
            ->with(['makam', 'permohonan'])
            ->when((auth()->user()?->isAdmin() || auth()->user()?->isKdlh() || auth()->user()?->isKepala()) && filled($selectedTpu) && in_array($selectedTpu, $tpuOptions, true), function ($query) use ($selectedTpu) {
                $query->where('tpu', $selectedTpu);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nama', 'like', "%$search%")
                        ->orWhere('nik', 'like', "%$search%")
                        ->orWhere('alamat', 'like', "%$search%");
                });
            });

        $jenazah = $jenazahQuery->latest()->paginate(10)->withQueryString();

        return view('pages.master.data_jenazah', [
            'jenazah' => $jenazah,
            'isPetugasView' => false,
            'filter' => $filter,
            'selectedTpu' => $selectedTpu,
            'tpuOptions' => $tpuOptions,
        ]);
    }

    public function create()
    {
        return view('pages.master.form_jenazah', [
            'jenazah' => new Jenazah(),
            'makams' => $this->getAccessibleMakams(),
            'selectedTpu' => request()->tpu,
            'tpuOptions' => User::tpuOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'nik' => ['required', 'string', 'max:255', 'unique:jenazah,nik'],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'agama' => ['nullable', 'string', 'max:100'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'tanggal_wafat' => ['required', 'date'],
            'alamat' => ['nullable', 'string'],
            'nama_ahli_waris' => ['nullable', 'string', 'max:255'],
            'hubungan_keluarga' => ['nullable', 'string', 'max:100'],
            'no_hp_ahli_waris' => ['nullable', 'string', 'max:50'],
            'catatan' => ['nullable', 'string'],
            'keterangan' => ['nullable', 'string'],
            'tpu' => ['nullable', Rule::in(User::tpuOptions())],
            'makam_id' => ['nullable', 'exists:makams,id'],
            'kode_makam' => ['nullable', 'string', 'max:255'],
            'blok' => ['nullable', 'string', 'max:255'],
            'zona' => ['nullable', 'string', 'max:255'],
            'nomor_makam' => ['nullable', 'string', 'max:255'],
            'tenggat_sewa_makam' => ['nullable', 'date'],
        ];

        if (auth()->user()?->isAdmin() || auth()->user()?->isKdlh()) {
            $rules['tpu'] = ['required', Rule::in(User::tpuOptions())];
        }

        $data = $request->validate($rules);

        $this->applyMakamSnapshot($request, $data);
        $this->validateAvailableMakam($request);
        $this->validateAccessibleMakam($request);
        $this->syncTenggatSewaField($data);
        $this->syncAhliWarisFields($data);

        if (auth()->user()?->isPetugas()) {
            $data['tpu'] = auth()->user()->tpu;
        } elseif (auth()->user()?->isAdmin()) {
            $data['tpu'] = $data['tpu'] ?? $request->input('tpu');
        }

        Jenazah::create($data);

        return redirect()->route($this->routePrefix() . '.data-jenazah')
            ->with('success', 'Data jenazah berhasil ditambahkan');
    }

    public function edit($id)
    {
        $jenazah = $this->findAccessibleJenazahOrFail($id);
        $jenazah->load(['permohonan', 'makam']);

        return view('pages.master.form_jenazah', [
            'jenazah' => $jenazah,
            'makams' => $this->getAccessibleMakams($jenazah),
            'selectedTpu' => request()->tpu,
            'tpuOptions' => User::tpuOptions(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $jenazah = $this->findAccessibleJenazahOrFail($id);

        $rules = [
            'nik' => ['required', 'string', 'max:255', Rule::unique('jenazah', 'nik')->ignore($id)],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'agama' => ['nullable', 'string', 'max:100'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'tanggal_wafat' => ['required', 'date'],
            'alamat' => ['nullable', 'string'],
            'keterangan' => ['nullable', 'string'],
            'tpu' => ['nullable', Rule::in(User::tpuOptions())],
            'makam_id' => ['nullable', 'exists:makams,id'],
            'kode_makam' => ['nullable', 'string', 'max:255'],
            'blok' => ['nullable', 'string', 'max:255'],
            'zona' => ['nullable', 'string', 'max:255'],
            'nomor_makam' => ['nullable', 'string', 'max:255'],
            'tenggat_sewa_makam' => ['nullable', 'date'],
            'nama_ahli_waris' => ['nullable', 'string', 'max:255'],
            'hubungan_keluarga' => ['nullable', 'string', 'max:100'],
            'no_hp_ahli_waris' => ['nullable', 'string', 'max:50'],
            'catatan' => ['nullable', 'string'],
        ];

        if (auth()->user()?->isAdmin() || auth()->user()?->isKdlh()) {
            $rules['tpu'] = ['required', Rule::in(User::tpuOptions())];
        }

        $data = $request->validate($rules);

        $this->applyMakamSnapshot($request, $data);
        $this->validateAvailableMakam($request, $jenazah);
        $this->validateAccessibleMakam($request);
        $this->syncTenggatSewaField($data, $jenazah);
        $this->syncAhliWarisFields($data);

        if (auth()->user()?->isPetugas()) {
            $data['tpu'] = auth()->user()->tpu;
        } elseif (auth()->user()?->isAdmin()) {
            $data['tpu'] = $data['tpu'] ?? $request->input('tpu');
        }

        DB::transaction(function () use ($jenazah, $data) {
            $jenazah->load('permohonan');
            $jenazah->update($data);

            if ($permohonan = $jenazah->permohonan) {
                $permohonan->update([
                    'nama_ahli_waris' => $data['nama_ahli_waris'] ?? null,
                    'hubungan_keluarga' => $data['hubungan_keluarga'] ?? null,
                    'no_hp_ahli_waris' => $data['no_hp_ahli_waris'] ?? null,
                    'catatan' => $data['catatan'] ?? null,
                    'tenggat_sewa_makam' => $data['tenggat_sewa_makam'] ?? $permohonan->tenggat_sewa_makam,
                ]);
            }
        });

        return redirect()->route($this->routePrefix() . '.data-jenazah')
            ->with('success', 'Data berhasil diupdate');
    }

    public function destroy($id)
    {
        $jenazah = $this->findAccessibleJenazahOrFail($id);

        DB::transaction(function () use ($jenazah) {
            Permohonan::where('jenazah_id', $jenazah->id)->delete();

            Permohonan::where('tpu', $jenazah->tpu)
                ->where('jenis_permohonan', 'makam_baru')
                ->where('status', 'disetujui')
                ->where(function ($query) use ($jenazah) {
                    $query->where('nik_jenazah', $jenazah->nik)
                        ->orWhere('nama_jenazah', $jenazah->nama);
                })
                ->delete();

            $jenazah->delete();
        });

        return redirect()->route($this->routePrefix() . '.data-jenazah')
            ->with('success', 'Data berhasil dihapus');
    }

    private function routePrefix(): string
    {
        if (request()->routeIs('petugas.*')) {
            return 'petugas';
        }

        if (request()->routeIs('kepala.*')) {
            return 'kepala';
        }

        if (request()->routeIs('kdlh.*')) {
            return 'kdlh';
        }

        return 'admin';
    }

    private function accessibleJenazah()
    {
        return Jenazah::query()->when(auth()->user()?->isPetugas(), function ($query) {
            $query->where('tpu', auth()->user()->tpu);
        });
    }

    private function findAccessibleJenazahOrFail(int $id): Jenazah
    {
        return $this->accessibleJenazah()->findOrFail($id);
    }

    private function getAccessibleMakams(?Jenazah $jenazah = null)
    {
        return Makam::query()
            ->when(auth()->user()?->isPetugas(), function ($query) {
                $query->where('tpu', auth()->user()->tpu);
            })
            ->where('status', 'kosong')
            ->when($jenazah, function ($query) use ($jenazah) {
                $query->orWhere('id', $jenazah->makam_id);
            })
            ->orderBy('kode_makam')
            ->get();
    }

    private function applyMakamSnapshot(Request $request, array &$data): void
    {
        $makam = $request->filled('makam_id') ? Makam::find($request->makam_id) : null;

        if (! $makam) {
            $data['kode_makam'] = $request->input('kode_makam');
            $data['blok'] = $request->input('blok');
            $data['zona'] = $request->input('zona');
            $data['nomor_makam'] = $request->input('nomor_makam');
            $data['keterangan'] = $request->input('keterangan');

            return;
        }

        $data['kode_makam'] = $makam->kode_makam;
        $data['blok'] = $makam->blok;
        $data['zona'] = $makam->zona;
        $data['nomor_makam'] = $makam->nomor;
        $data['keterangan'] = $makam->keterangan;
    }

    private function validateAccessibleMakam(Request $request): void
    {
        if (! $request->filled('makam_id')) {
            return;
        }

        $makam = Makam::findOrFail($request->makam_id);

        if (auth()->user()?->isPetugas() && $makam->tpu !== auth()->user()->tpu) {
            throw ValidationException::withMessages([
                'makam_id' => 'Anda hanya dapat memilih makam dari TPU Anda sendiri.',
            ]);
        }

        if ((auth()->user()?->isAdmin() || auth()->user()?->isKdlh()) && filled($request->tpu) && $makam->tpu !== $request->tpu) {
            throw ValidationException::withMessages([
                'tpu' => 'TPU yang dipilih harus sama dengan TPU dari makam.',
            ]);
        }
    }

    private function validateAvailableMakam(Request $request, ?Jenazah $jenazah = null): void
    {
        if (! $request->filled('makam_id')) {
            return;
        }

        $makam = Makam::findOrFail($request->makam_id);

        if ($makam->status === 'terisi' && (int) $makam->id !== (int) $jenazah?->makam_id) {
            throw ValidationException::withMessages([
                'makam_id' => 'Makam yang dipilih sudah terisi.',
            ]);
        }
    }

    private function syncTenggatSewaField(array &$data, ?Jenazah $jenazah = null): void
    {
        if (! Schema::hasColumn('jenazah', 'tenggat_sewa_makam')) {
            unset($data['tenggat_sewa_makam']);
            return;
        }

        // Kalau field tidak diisi/dikirim kosong, pertahankan nilai lama (kalau sedang edit)
        if (blank($data['tenggat_sewa_makam'] ?? null)) {
            $data['tenggat_sewa_makam'] = $jenazah?->tenggat_sewa_makam;
            return;
        }
    }

    private function syncAhliWarisFields(array &$data): void
    {
        foreach (['nama_ahli_waris', 'hubungan_keluarga', 'no_hp_ahli_waris', 'catatan'] as $field) {
            $data[$field] = $data[$field] ?? null;
        }
    }

    private function syncApprovedPermohonanJenazah(): void
    {
        $query = Permohonan::with(['jenazah', 'makam'])
            ->where('status', 'disetujui')
            ->where('jenis_permohonan', 'makam_baru')
            ->whereNotNull('nama_jenazah')
            ->whereNotNull('nik_jenazah')
            ->whereNotNull('jenis_kelamin')
            ->whereNotNull('tanggal_wafat')
            ->when(auth()->user()?->isPetugas(), function ($subQuery) {
                $subQuery->where('tpu', auth()->user()->tpu);
            })
            ->when(Schema::hasColumn('permohonans', 'jenazah_deleted_at'), function ($subQuery) {
                $subQuery->whereNull('jenazah_deleted_at');
            });

        $query->get()->each(function (Permohonan $permohonan) {
            $permohonan->persistJenazahRecord();
        });
    }
}
