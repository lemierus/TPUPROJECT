<?php

namespace App\Http\Controllers\Kdlh;

use App\Http\Controllers\Controller;
use App\Models\Tpu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TpuController extends Controller
{
    public function index()
    {
        $tpus = Tpu::with('waPetugas')->orderBy('nama')->get();

        return view('kdlh.tpu.index', compact('tpus'));
    }

    public function create()
    {
        return view('kdlh.tpu.form', [
            'tpu' => new Tpu(),
            'petugasList' => User::where('role', User::ROLE_PETUGAS)
                ->whereNotNull('no_hp')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $biayaSewaItems = $this->validatedBiayaSewa($request);

        $tpu = Tpu::create($data);

        $this->syncBiayaSewa($tpu, $biayaSewaItems);

        return redirect()->route('kdlh.tpu.index')->with('success', 'Data TPU berhasil ditambahkan');
    }

    public function edit(Tpu $tpu)
    {
        return view('kdlh.tpu.form', [
            'tpu' => $tpu->load('biayaSewas'),
            'petugasList' => User::where('role', User::ROLE_PETUGAS)
                ->whereNotNull('no_hp')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, Tpu $tpu)
    {
        $data = $this->validatedData($request, $tpu->id);
        $biayaSewaItems = $this->validatedBiayaSewa($request);

        $tpu->update($data);

        $this->syncBiayaSewa($tpu, $biayaSewaItems);

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
            'deskripsi' => ['nullable', 'string'],
            'wa_petugas_id' => ['nullable', 'exists:users,id'],
        ]);
    }

    /**
     * Validasi baris-baris "Biaya Sewa Makam" yang diinput kepala dinas.
     * Baris kosong (tanpa label atau harga) diabaikan agar repeater di sisi
     * client bisa bebas menambah/menghapus baris tanpa memicu error validasi.
     */
    private function validatedBiayaSewa(Request $request): array
    {
        $validated = $request->validate([
            'biaya_sewa' => ['nullable', 'array'],
            'biaya_sewa.*.label' => ['nullable', 'string', 'max:255'],
            'biaya_sewa.*.harga' => ['nullable', 'numeric', 'min:0'],
        ]);

        return collect($validated['biaya_sewa'] ?? [])
            ->filter(fn ($item) => filled($item['label'] ?? null) && $item['harga'] !== null && $item['harga'] !== '')
            ->map(fn ($item) => [
                'label' => trim($item['label']),
                'harga' => (int) $item['harga'],
            ])
            ->values()
            ->all();
    }

    /**
     * Sinkronkan daftar biaya sewa TPU dengan pola replace-all: hapus semua
     * baris lama lalu buat ulang dari input terbaru. Aman karena baris biaya
     * sewa tidak direferensikan oleh tabel lain lewat foreign key (permohonan
     * hanya menyimpan label sebagai teks, bukan relasi ke baris ini).
     */
    private function syncBiayaSewa(Tpu $tpu, array $items): void
    {
        $tpu->biayaSewas()->delete();

        foreach ($items as $item) {
            $tpu->biayaSewas()->create($item);
        }
    }
}