<?php

namespace App\Http\Controllers\Kdlh;

use App\Http\Controllers\Controller;
use App\Models\BiayaRetribusi;
use Illuminate\Http\Request;

class BiayaRetribusiController extends Controller
{
    public function index()
    {
        $biayaRetribusis = BiayaRetribusi::query()
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('kdlh.biaya_retribusi.index', compact('biayaRetribusis'));
    }

    public function create()
    {
        return view('kdlh.biaya_retribusi.form', [
            'biayaRetribusi' => new BiayaRetribusi(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        BiayaRetribusi::create($data);

        return redirect()->route('kdlh.biaya-retribusi.index')
            ->with('success', 'Biaya retribusi berhasil ditambahkan.');
    }

    public function edit(BiayaRetribusi $biayaRetribusi)
    {
        return view('kdlh.biaya_retribusi.form', compact('biayaRetribusi'));
    }

    public function update(Request $request, BiayaRetribusi $biayaRetribusi)
    {
        $data = $this->validatedData($request);

        $biayaRetribusi->update($data);

        return redirect()->route('kdlh.biaya-retribusi.index')
            ->with('success', 'Biaya retribusi berhasil diperbarui.');
    }

    public function destroy(BiayaRetribusi $biayaRetribusi)
    {
        $biayaRetribusi->delete();

        return redirect()->route('kdlh.biaya-retribusi.index')
            ->with('success', 'Biaya retribusi berhasil dihapus.');
    }

    public function toggle(BiayaRetribusi $biayaRetribusi)
    {
        $biayaRetribusi->update([
            'is_aktif' => ! $biayaRetribusi->is_aktif,
        ]);

        return redirect()->route('kdlh.biaya-retribusi.index')
            ->with('success', 'Status biaya retribusi berhasil diperbarui.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'nama_biaya' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'numeric', 'min:0'],
            'nomor_rekening' => ['required', 'string', 'max:255'],
            'nama_bank' => ['required', 'string', 'max:255'],
            'atas_nama_rekening' => ['required', 'string', 'max:255'],
            'is_aktif' => ['nullable', 'boolean'],
        ], [
            'nama_biaya.required' => 'Nama/kategori biaya wajib diisi.',
            'nominal.required' => 'Nominal biaya wajib diisi.',
            'nominal.numeric' => 'Nominal biaya harus berupa angka.',
            'nominal.min' => 'Nominal biaya tidak boleh kurang dari 0.',
            'nomor_rekening.required' => 'Nomor rekening tujuan wajib diisi.',
            'nama_bank.required' => 'Nama bank wajib diisi.',
            'atas_nama_rekening.required' => 'Atas nama rekening wajib diisi.',
        ]);
    }
}
