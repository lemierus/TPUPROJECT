<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jenazah;
use App\Models\Makam;
use App\Models\Permohonan;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DataJenazahController extends Controller
{
    // ✅ TAMPILKAN DATA + SEARCH
    public function index(Request $request)
    {
        $search = $request->search;
        $filter = $request->filter ?? 'harian';

        if (auth()->user()?->isPetugas()) {
            $permohonanQuery = Permohonan::with(['makam', 'user', 'jenazah'])
                ->where('tpu', auth()->user()->tpu)
                ->where('status', 'disetujui')
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($subQuery) use ($search) {
                        $subQuery->where('nama_jenazah', 'like', "%$search%")
                            ->orWhere('nik_jenazah', 'like', "%$search%")
                            ->orWhere('nama_ahli_waris', 'like', "%$search%")
                            ->orWhere('no_hp_ahli_waris', 'like', "%$search%")
                            ->orWhere('hubungan_keluarga', 'like', "%$search%");
                    });
                });

            if ($filter === 'mingguan') {
                $permohonanQuery->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ]);
            } elseif ($filter === 'bulanan') {
                $permohonanQuery->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
            } else {
                $permohonanQuery->whereDate('created_at', Carbon::today());
            }

            $permohonanJenazah = $permohonanQuery
                ->latest()
                ->get()
                ->groupBy(function ($item) use ($filter) {
                    if ($filter === 'mingguan') {
                        return Carbon::parse($item->created_at)->startOfWeek()->format('Y-m-d');
                    }

                    if ($filter === 'bulanan') {
                        return Carbon::parse($item->created_at)->format('Y-m');
                    }

                    return Carbon::parse($item->created_at)->format('Y-m-d');
                });

            return view('pages.master.data_jenazah', [
                'permohonanJenazah' => $permohonanJenazah,
                'isPetugasView' => true,
                'filter' => $filter,
            ]);
        }

        $jenazah = $this->accessibleJenazah()
            ->with('makam')
            ->when($search, function ($query) use ($search) {
                $query->where('nama', 'like', "%$search%")
                      ->orWhere('nik', 'like', "%$search%")
                      ->orWhere('alamat', 'like', "%$search%");
            })->latest()->get();

        return view('pages.master.data_jenazah', [
            'jenazah' => $jenazah,
            'isPetugasView' => false,
            'filter' => $filter,
        ]);
    }

    public function create()
    {
        return view('pages.master.form_jenazah', [
            'jenazah' => new Jenazah(),
            'makams' => $this->getAccessibleMakams(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nik' => ['required', 'string', 'max:255', 'unique:jenazah,nik'],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'agama' => ['nullable', 'string', 'max:100'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'tanggal_wafat' => ['required', 'date'],
            'alamat' => ['nullable', 'string'],
            'keterangan' => ['nullable', 'string'],
            'makam_id' => ['nullable', 'exists:makams,id'],
        ]);

        $this->validateAvailableMakam($request);
        $this->validateAccessibleMakam($request);

        if (auth()->user()?->isPetugas()) {
            $data['tpu'] = auth()->user()->tpu;
        }

        Jenazah::create($data);

        return redirect()->route($this->routePrefix().'.data-jenazah')
            ->with('success', 'Data jenazah berhasil ditambahkan');
    }

    public function edit($id)
    {
        $jenazah = $this->findAccessibleJenazahOrFail($id);
        $jenazah->load('permohonan');

        return view('pages.master.form_jenazah', [
            'jenazah' => $jenazah,
            'makams' => $this->getAccessibleMakams($jenazah),
        ]);
    }

    public function update(Request $request, $id)
    {
        $jenazah = $this->findAccessibleJenazahOrFail($id);

        $data = $request->validate([
            'nik' => ['required', 'string', 'max:255', Rule::unique('jenazah', 'nik')->ignore($id)],
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'agama' => ['nullable', 'string', 'max:100'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'tanggal_wafat' => ['required', 'date'],
            'alamat' => ['nullable', 'string'],
            'keterangan' => ['nullable', 'string'],
            'makam_id' => ['nullable', 'exists:makams,id'],
            'nama_ahli_waris' => ['nullable', 'string', 'max:255'],
            'hubungan_keluarga' => ['nullable', 'string', 'max:100'],
            'no_hp_ahli_waris' => ['nullable', 'string', 'max:50'],
            'catatan' => ['nullable', 'string'],
        ]);

        $this->validateAvailableMakam($request, $jenazah);
        $this->validateAccessibleMakam($request);

        $jenazah->load('permohonan');
        $jenazah->update($data);

        if ($permohonan = $jenazah->permohonan) {
            $permohonan->update([
                'nama_ahli_waris' => $data['nama_ahli_waris'] ?? null,
                'hubungan_keluarga' => $data['hubungan_keluarga'] ?? null,
                'no_hp_ahli_waris' => $data['no_hp_ahli_waris'] ?? null,
                'catatan' => $data['catatan'] ?? null,
            ]);
        }

        return redirect()->route($this->routePrefix().'.data-jenazah')
            ->with('success', 'Data berhasil diupdate');
    }

    public function destroy($id)
    {
        $jenazah = $this->findAccessibleJenazahOrFail($id);
        $jenazah->delete();

        return redirect()->route($this->routePrefix().'.data-jenazah')
            ->with('success', 'Data berhasil dihapus');
    }

    private function routePrefix(): string
    {
        return request()->routeIs('petugas.*') ? 'petugas' : 'admin';
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

    private function validateAccessibleMakam(Request $request): void
    {
        if (! $request->filled('makam_id') || ! auth()->user()?->isPetugas()) {
            return;
        }

        $makam = Makam::findOrFail($request->makam_id);

        if ($makam->tpu !== auth()->user()->tpu) {
            throw ValidationException::withMessages([
                'makam_id' => 'Anda hanya dapat memilih makam dari TPU Anda sendiri.',
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
}
