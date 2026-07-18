<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Makam;
use App\Models\User;
use Illuminate\Validation\Rule;

class DataMakamController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $selectedTpu = $request->tpu;
        $tpuOptions = User::tpuOptions();

        $makamQuery = $this->accessibleMakams()
            ->when((auth()->user()?->isAdmin() || auth()->user()?->isKdlh() || auth()->user()?->isKepala()) && filled($selectedTpu) && in_array($selectedTpu, $tpuOptions, true), function ($query) use ($selectedTpu) {
                $query->where('tpu', $selectedTpu);
            })
            ->when($search, function ($query) use ($search) {
                // PENTING: seluruh kondisi orWhere() di sini DIBUNGKUS dalam satu
                // closure where() supaya jadi satu grup "(...)" di SQL. Kalau tidak
                // dibungkus, orWhere akan "keluar" dari grouping filter tpu milik
                // petugas (accessibleMakams()) sehingga petugas bisa menemukan data
                // makam TPU lain selama kata kuncinya cocok di kolom selain 'tpu'.
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('tpu', 'like', "%$search%")
                        ->orWhere('kode_makam', 'like', "%$search%")
                        ->orWhere('blok', 'like', "%$search%")
                        ->orWhere('status', 'like', "%$search%")
                        ->orWhere('nomor', 'like', "%$search%")
                        ->orWhere('keterangan', 'like', "%$search%");
                });
            });

        $makams = $makamQuery->latest()->paginate(10)->withQueryString();

        return view('pages.master.data_makam', compact('makams', 'selectedTpu', 'tpuOptions'));
    }

    public function create()
    {
        $this->ensureCanManageMakams();

        return view('pages.master.form_makam', [
            'makam' => new Makam(),
            'selectedTpu' => request()->tpu,
            'tpuOptions' => User::tpuOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureCanManageMakams();

        Makam::create($this->validatedData($request));

        return redirect()->route($this->routePrefix().'.data-makam')
            ->with('success', 'Data makam berhasil ditambahkan');
    }

    public function edit(Makam $makam)
    {
        $this->ensureCanManageMakams();

        $makam = $this->findAccessibleMakamOrFail($makam->id);

        return view('pages.master.form_makam', [
            'makam' => $makam,
            'selectedTpu' => request()->tpu,
            'tpuOptions' => User::tpuOptions(),
        ]);
    }

    public function update(Request $request, Makam $makam)
    {
        $this->ensureCanManageMakams();

        $makam = $this->findAccessibleMakamOrFail($makam->id);
        $makam->update($this->validatedData($request, $makam->id));

        return redirect()->route($this->routePrefix().'.data-makam')
            ->with('success', 'Data makam berhasil diperbarui');
    }

    public function destroy(Makam $makam)
    {
        $this->ensureCanManageMakams();

        $makam = $this->findAccessibleMakamOrFail($makam->id);
        $makam->delete();

        return redirect()->route($this->routePrefix().'.data-makam')
            ->with('success', 'Data makam berhasil dihapus');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'tpu' => ['required', Rule::in(User::tpuOptions())],
            'kode_makam' => ['required', 'string', 'max:255', Rule::unique('makams', 'kode_makam')->ignore($ignoreId)],
            'blok' => ['nullable', 'string', 'max:255'],
            'zona' => ['nullable', 'string', 'max:255'],
            'nomor' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:kosong,terisi'],
            'keterangan' => ['nullable', 'string'],
        ]);

        if (auth()->user()?->isPetugas()) {
            $data['tpu'] = auth()->user()->tpu;
        }

        return $data;
    }

    private function routePrefix(): string
    {
        if (request()->routeIs('petugas.*')) {
            return 'petugas';
        }

        if (request()->routeIs('kepala.*')) {
            return 'kepala';
        }

        return 'admin';
    }

    private function accessibleMakams()
    {
        return Makam::query()->when(auth()->user()?->isPetugas(), function ($query) {
            $query->where('tpu', auth()->user()->tpu);
        });
    }

    private function findAccessibleMakamOrFail(int $id): Makam
    {
        return $this->accessibleMakams()->findOrFail($id);
    }

    private function ensureCanManageMakams(): void
    {
        abort_unless(auth()->user()?->isAdmin() || auth()->user()?->isKepala() || auth()->user()?->isKdlh(), 403);
    }
}