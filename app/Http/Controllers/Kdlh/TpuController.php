<?php

namespace App\Http\Controllers\Kdlh;

use App\Http\Controllers\Controller;
use App\Models\Tpu;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TpuController extends Controller
{
    public function index()
    {
        $tpus = Tpu::query()->orderBy('nama')->get();

        return view('kdlh.tpu.index', compact('tpus'));
    }

    public function create()
    {
        return view('kdlh.tpu.form', [
            'tpu' => new Tpu(),
        ]);
    }

    public function store(Request $request)
    {
        Tpu::create($this->validatedData($request));

        return redirect()->route('kdlh.tpu.index')->with('success', 'Data TPU berhasil ditambahkan');
    }

    public function edit(Tpu $tpu)
    {
        return view('kdlh.tpu.form', compact('tpu'));
    }

    public function update(Request $request, Tpu $tpu)
    {
        $tpu->update($this->validatedData($request, $tpu->id));

        return redirect()->route('kdlh.tpu.index')->with('success', 'Data TPU berhasil diperbarui');
    }

    public function destroy(Tpu $tpu)
    {
        $tpu->delete();

        return redirect()->route('kdlh.tpu.index')->with('success', 'Data TPU berhasil dihapus');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('tpus', 'nama')->ignore($ignoreId)],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'ringkasan' => ['nullable', 'string'],
            'highlight' => ['nullable', 'string'],
            'deskripsi' => ['nullable', 'string']
        ]);
    }
}
