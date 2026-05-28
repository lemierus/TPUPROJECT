<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Jenazah;
use App\Models\Laporan;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->filter ?? 'harian';
        $start  = $request->start;
        $end    = $request->end;

        $query = Jenazah::with('makam')
            ->when(auth()->user()?->isPetugas(), function ($query) {
                $query->where('tpu', auth()->user()->tpu);
            });

        if ($filter == 'harian') {
            $query->whereDate('tanggal_wafat', Carbon::today());
        } elseif ($filter == 'mingguan') {
            $query->whereBetween('tanggal_wafat', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ]);
        } elseif ($filter == 'bulanan') {
            $query->whereMonth('tanggal_wafat', Carbon::now()->month);
        } elseif ($filter == 'tahunan') {
            $query->whereYear('tanggal_wafat', Carbon::now()->year);
        }

        // FILTER CUSTOM RANGE
        if ($start && $end) {
            $query->whereBetween('tanggal_wafat', [$start, $end]);
        }

        $data = $query->get();

        return view('pages.master.laporan', [
            'data' => $data,
            'total' => $data->count(),
            'laki' => $data->whereIn('jenis_kelamin', ['L', 'Laki-laki'])->count(),
            'perempuan' => $data->whereIn('jenis_kelamin', ['P', 'Perempuan'])->count(),
            'totalMakamTerisi' => $data->whereNotNull('makam_id')->count(),
        ]);
    }

    public function create()
    {
        return view('pages.master.form_laporan', [
            'laporan' => new Laporan(),
        ]);
    }

    public function store(Request $request)
    {
        Laporan::create($this->validatedData($request));

        return redirect()->route($this->routePrefix() . '.master.laporan')
            ->with('success', 'Laporan berhasil ditambahkan');
    }

    public function edit(Laporan $laporan)
    {
        return view('pages.master.form_laporan', compact('laporan'));
    }

    public function update(Request $request, Laporan $laporan)
    {
        $laporan->update($this->validatedData($request));

        return redirect()->route($this->routePrefix() . '.master.laporan')
            ->with('success', 'Laporan berhasil diperbarui');
    }

    public function destroy(Laporan $laporan)
    {
        $laporan->delete();

        return redirect()->route($this->routePrefix() . '.master.laporan')
            ->with('success', 'Laporan berhasil dihapus');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'nama_jenazah' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tanggal_wafat' => ['required', 'date'],
            'makam' => ['nullable', 'string', 'max:255'],
            'blok' => ['nullable', 'string', 'max:255'],
            'zona' => ['nullable', 'string', 'max:255'],
            'periode' => ['required', 'in:harian,mingguan,bulanan,tahunan'],
        ]);
    }

    private function routePrefix(): string
    {
        return request()->routeIs('petugas.*') ? 'petugas' : 'admin';
    }
}
