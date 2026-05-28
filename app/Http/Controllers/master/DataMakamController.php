<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Makam;
use Illuminate\Validation\Rule;

class DataMakamController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $makams = $this->accessibleMakams()->when($search, function ($query) use ($search) {
            $query->where('tpu', 'like', "%$search%")
                ->orWhere('kode_makam', 'like', "%$search%")
                ->orWhere('blok', 'like', "%$search%")
                ->orWhere('zona', 'like', "%$search%")
                ->orWhere('nomor', 'like', "%$search%");
        })->latest()->get();

        return view('pages.master.data_makam', compact('makams'));
    }

    public function create()
    {
        return view('pages.master.form_makam', [
            'makam' => new Makam(),
        ]);
    }

    public function store(Request $request)
    {
        Makam::create($this->validatedData($request));

        return redirect()->route($this->routePrefix().'.data-makam')
            ->with('success', 'Data makam berhasil ditambahkan');
    }

    public function edit(Makam $makam)
    {
        $makam = $this->findAccessibleMakamOrFail($makam->id);

        return view('pages.master.form_makam', compact('makam'));
    }

    public function update(Request $request, Makam $makam)
    {
        $makam = $this->findAccessibleMakamOrFail($makam->id);
        $makam->update($this->validatedData($request, $makam->id));

        return redirect()->route($this->routePrefix().'.data-makam')
            ->with('success', 'Data makam berhasil diperbarui');
    }

    public function destroy(Makam $makam)
    {
        $makam = $this->findAccessibleMakamOrFail($makam->id);
        $makam->delete();

        return redirect()->route($this->routePrefix().'.data-makam')
            ->with('success', 'Data makam berhasil dihapus');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'tpu' => ['required', Rule::in(['TPU Tunggul Hitam', 'TPU Bungus Teluk Kabung', 'TPU Air Dingin'])],
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
        return request()->routeIs('petugas.*') ? 'petugas' : 'admin';
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
}
