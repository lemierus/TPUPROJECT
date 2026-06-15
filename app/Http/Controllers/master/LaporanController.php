<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Jenazah;
use App\Models\Laporan;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->filter ?? 'harian';
        $start  = $request->start;
        $end    = $request->end;
        $selectedTpu = $request->tpu;
        $tpuOptions = User::tpuOptions();

        if (auth()->user()?->isPetugas() || auth()->user()?->isKepala()) {
            $report = $this->buildPetugasReport($filter, $start, $end);

            return view('pages.master.laporan', array_merge($report, [
                'routePrefix' => 'petugas',
                'isPetugasReport' => true,
                'isKepalaReport' => false,
                'wordRoute' => route('petugas.master.laporan.word', $request->query()),
                'exportExcelRoute' => route('petugas.master.laporan.excel', $request->query()),
                'printRoute' => route('petugas.master.laporan.print', $request->query()),
                'filter' => $filter,
                'start' => $start,
                'end' => $end,
            ]));
        }

        $query = Jenazah::with('makam')
            ->when(auth()->user()?->isPetugas(), function ($query) {
                $query->where('tpu', auth()->user()->tpu);
            })
            ->when(auth()->user()?->isAdmin() && filled($selectedTpu) && in_array($selectedTpu, $tpuOptions, true), function ($query) use ($selectedTpu) {
                $query->where('tpu', $selectedTpu);
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
            'isPetugasReport' => false,
            'selectedTpu' => $selectedTpu,
            'tpuOptions' => $tpuOptions,
            'wordRoute' => route('admin.master.laporan.word', $request->query()),
            'printRoute' => route('admin.master.laporan.print', $request->query()),
            'exportExcelRoute' => route('admin.master.laporan.excel', $request->query()),
            'routePrefix' => request()->routeIs('petugas.*') ? 'petugas' : 'admin',
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

    public function print(Request $request)
    {
        $filter = $request->filter ?? 'harian';
        $start = $request->start;
        $end = $request->end;

        abort_unless(auth()->user()?->isPetugas() || auth()->user()?->isKepala(), 403);
        $report = auth()->user()?->isKepala()
            ? $this->buildKepalaReport($filter, $start, $end)
            : $this->buildPetugasReport($filter, $start, $end);

        return view('pages.master.laporan_print', array_merge($report, [
            'filter' => $filter,
            'start' => $start,
            'end' => $end,
        ]));
    }

    public function word(Request $request)
    {
        $filter = $request->filter ?? 'harian';
        $start = $request->start;
        $end = $request->end;

        abort_unless(auth()->user()?->isAdmin() || auth()->user()?->isPetugas() || auth()->user()?->isKepala(), 403);
        $report = $this->buildWordReport($filter, $start, $end, $request->tpu);

        $prefix = auth()->user()->isAdmin() ? 'laporan-admin' : (auth()->user()->isKepala() ? 'laporan-kepala' : 'laporan-petugas');
        $scopeSlug = Str::slug($report['scopeLabel'] ?? auth()->user()->tpu ?? 'semua-tpu');
        $filename = $prefix . '-' . $scopeSlug . '-' . now()->format('Ymd-His') . '.doc';

        $html = view('pages.master.laporan_word', array_merge($report, [
            'filter' => $filter,
            'start' => $start,
            'end' => $end,
        ]))->render();

        return response($html, 200, [
            'Content-Type' => 'application/msword; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function excel(Request $request)
    {
        $filter = $request->filter ?? 'harian';
        $start = $request->start;
        $end = $request->end;

        abort_unless(auth()->user()?->isPetugas() || auth()->user()?->isKepala(), 403);
        $report = auth()->user()->isKepala()
            ? $this->buildKepalaReport($filter, $start, $end)
            : $this->buildPetugasReport($filter, $start, $end);
        $prefix = auth()->user()->isKepala() ? 'laporan-kepala' : 'laporan-petugas';
        $filename = $prefix . '-' . auth()->user()->tpu . '-' . now()->format('Ymd-His') . '.xls';

        $html = view('pages.master.laporan_export', $report + [
            'filter' => $filter,
            'start' => $start,
            'end' => $end,
        ])->render();

        return response()->streamDownload(function () use ($html) {
            echo $html;
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
        ]);
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

    private function buildPetugasReport(string $filter, ?string $start, ?string $end): array
    {
        $tpu = auth()->user()->tpu;

        $permohonanQuery = Permohonan::with(['makam', 'jenazah', 'user'])
            ->where('tpu', $tpu);

        $this->applyDateFilter($permohonanQuery, $filter, $start, $end, 'created_at');

        $permohonans = $permohonanQuery->latest('created_at')->get();

        $jenazahQuery = Jenazah::with('makam')
            ->where('tpu', $tpu);

        $this->applyDateFilter($jenazahQuery, $filter, $start, $end, 'tanggal_wafat');

        $jenazahs = $jenazahQuery->latest('created_at')->get();

        $rows = $this->buildReportRows($permohonans, $jenazahs)->sortByDesc('tanggal_sort')->values();

        return [
            'isPetugasReport' => true,
            'reportRows' => $rows,
            'permohonansReport' => $permohonans,
            'jenazahsReport' => $jenazahs,
            'total' => $rows->count(),
            'totalPermohonan' => $permohonans->count(),
            'totalJenazah' => $jenazahs->count(),
            'totalPemakaman' => $jenazahs->count(),
            'totalMakamTerhubung' => $rows->filter(fn ($row) => ! empty($row['kode_makam']) || ! empty($row['nomor_makam']))->count(),
            'permohonanMenunggu' => $permohonans->whereIn('status', ['menunggu', 'pending'])->count(),
            'permohonanDisetujui' => $permohonans->where('status', 'disetujui')->count(),
            'permohonanDitolak' => $permohonans->where('status', 'ditolak')->count(),
            'laki' => $rows->whereIn('jenis_kelamin', ['L', 'Laki-laki'])->count(),
            'perempuan' => $rows->whereIn('jenis_kelamin', ['P', 'Perempuan'])->count(),
        ];
    }

    private function buildReportRows(Collection $permohonans, Collection $jenazahs): Collection
    {
        $permohonanRows = $permohonans->map(function (Permohonan $item) {
            $makam = $item->makam ?: $item->jenazah?->makam;

            return [
                'source' => 'permohonan',
                'source_label' => $item->jenis_permohonan === 'perpanjangan' ? 'Permohonan Perpanjangan' : 'Permohonan Makam Baru',
                'jenis_permohonan' => $item->jenis_permohonan ?? '-',
                'nama' => $item->nama_jenazah ?? $item->jenazah?->nama ?? '-',
                'nik' => $item->nik_jenazah ?? $item->jenazah?->nik ?? '-',
                'jenis_kelamin' => $item->jenis_kelamin ?? $item->jenazah?->jenis_kelamin ?? '-',
                'tanggal_wafat_label' => $this->formatDate($item->tanggal_wafat ?? $item->jenazah?->tanggal_wafat),
                'tanggal_input_label' => $this->formatDate($item->created_at),
                'tanggal_sort' => $item->created_at?->timestamp ?? 0,
                'kode_makam' => $item->kode_makam ?? $makam?->kode_makam ?? '-',
                'blok' => $item->blok ?? $makam?->blok ?? '-',
                'zona' => $item->zona ?? $makam?->zona ?? '-',
                'nomor_makam' => $item->nomor_makam ?? $makam?->nomor ?? '-',
                'status_makam' => $makam?->status ?? '-',
                'status_permohonan' => $item->status ?? '-',
                'catatan' => $item->catatan ?? '-',
                'nama_ahli_waris' => $item->nama_ahli_waris ?? '-',
                'no_hp_ahli_waris' => $item->no_hp_ahli_waris ?? '-',
                'hubungan_keluarga' => $item->hubungan_keluarga ?? '-',
                'tpu' => $item->tpu ?? '-',
            ];
        });

        $jenazahRows = $jenazahs->map(function (Jenazah $item) {
            $makam = $item->makam;

            return [
                'source' => 'jenazah',
                'source_label' => 'Data Jenazah',
                'nama' => $item->nama ?? '-',
                'nik' => $item->nik ?? '-',
                'jenis_kelamin' => $item->jenis_kelamin ?? '-',
                'tanggal_wafat_label' => $this->formatDate($item->tanggal_wafat),
                'tanggal_input_label' => $this->formatDate($item->created_at),
                'tanggal_sort' => optional($item->tanggal_wafat)->timestamp ?? 0,
                'kode_makam' => $item->kode_makam ?? $makam?->kode_makam ?? '-',
                'blok' => $item->blok ?? $makam?->blok ?? '-',
                'zona' => $item->zona ?? $makam?->zona ?? '-',
                'nomor_makam' => $item->nomor_makam ?? $makam?->nomor ?? '-',
                'status_makam' => $makam?->status ?? '-',
                'status_permohonan' => '-',
                'catatan' => $item->keterangan ?? '-',
                'nama_ahli_waris' => '-',
                'no_hp_ahli_waris' => '-',
                'hubungan_keluarga' => '-',
                'tpu' => $item->tpu ?? '-',
            ];
        });

        return $permohonanRows->concat($jenazahRows);
    }

    private function applyDateFilter($query, string $filter, ?string $start, ?string $end, string $dateColumn): void
    {
        if ($start && $end) {
            $query->whereBetween($dateColumn, [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay(),
            ]);
            return;
        }

        if ($filter === 'harian') {
            $query->whereDate($dateColumn, Carbon::today());
        } elseif ($filter === 'mingguan') {
            $query->whereBetween($dateColumn, [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ]);
        } elseif ($filter === 'bulanan') {
            $query->whereMonth($dateColumn, Carbon::now()->month)
                ->whereYear($dateColumn, Carbon::now()->year);
        } elseif ($filter === 'tahunan') {
            $query->whereYear($dateColumn, Carbon::now()->year);
        }
    }

    private function formatDate($value): string
    {
        if (empty($value)) {
            return '-';
        }

        return Carbon::parse($value)->format('d-m-Y');
    }

    private function buildWordReport(string $filter, ?string $start, ?string $end, ?string $selectedTpu = null): array
    {
        $scopeTpu = auth()->user()?->isAdmin() ? (filled($selectedTpu) ? $selectedTpu : null) : auth()->user()?->tpu;
        $scopeLabel = $scopeTpu ?: 'Semua TPU';

        $permohonanQuery = Permohonan::with(['makam', 'jenazah', 'user']);
        if ($scopeTpu) {
            $permohonanQuery->where('tpu', $scopeTpu);
        }
        $this->applyDateFilter($permohonanQuery, $filter, $start, $end, 'created_at');
        $permohonans = $permohonanQuery->latest('created_at')->get();

        $jenazahQuery = Jenazah::with('makam');
        if ($scopeTpu) {
            $jenazahQuery->where('tpu', $scopeTpu);
        }
        $this->applyDateFilter($jenazahQuery, $filter, $start, $end, 'tanggal_wafat');
        $jenazahs = $jenazahQuery->latest('created_at')->get();

        $rows = $this->buildReportRows($permohonans, $jenazahs)->sortByDesc('tanggal_sort')->values();

        $permohonanBaru = $permohonans->where('jenis_permohonan', 'makam_baru')->count();
        $perpanjangan = $permohonans->where('jenis_permohonan', 'perpanjangan')->count();
        $perawatan = 0;
        $makamAktif = $jenazahs->whereNotNull('makam_id')->count();
        $berakhirSewa = $jenazahs->filter(function (Jenazah $item) {
            $due = $item->renewalDueAt();
            return $due && $due->isPast();
        })->count();

        $dataJenazahRows = $rows->where('source', 'permohonan')->values();
        if ($dataJenazahRows->isEmpty()) {
            $dataJenazahRows = $rows->values();
        }

        $blokZonaStats = $rows
            ->groupBy(fn ($row) => trim(($row['blok'] ?? '-') . '/' . ($row['zona'] ?? '-')))
            ->map(fn ($items, $key) => [
                'blok_zona' => str_replace('/', ' / ', $key),
                'jumlah' => $items->count(),
            ])
            ->sortByDesc('jumlah')
            ->values();

        return [
            'reportRows' => $rows,
            'permohonansReport' => $permohonans,
            'jenazahsReport' => $jenazahs,
            'scopeLabel' => $scopeLabel,
            'periodLabel' => $this->reportPeriodLabel($filter, $start, $end),
            'monthLabel' => $this->reportMonthLabel($filter, $start, $end),
            'yearLabel' => $this->reportYearLabel($filter, $start, $end),
            'totalPemakamanBaru' => $permohonanBaru,
            'totalPerpanjangan' => $perpanjangan,
            'totalPerawatan' => $perawatan,
            'totalMakamAktif' => $makamAktif,
            'totalMakamBerakhirSewa' => $berakhirSewa,
            'dataJenazahRows' => $dataJenazahRows,
            'blokZonaStats' => $blokZonaStats,
            'reportDate' => now()->translatedFormat('d F Y'),
        ];
    }

    private function reportPeriodLabel(string $filter, ?string $start, ?string $end): string
    {
        if ($start && $end) {
            return Carbon::parse($start)->format('d F Y') . ' s/d ' . Carbon::parse($end)->format('d F Y');
        }

        return match ($filter) {
            'harian' => 'Harian',
            'mingguan' => 'Mingguan',
            'bulanan' => 'Bulanan',
            'tahunan' => 'Tahunan',
            default => 'Bulanan',
        };
    }

    private function reportMonthLabel(string $filter, ?string $start, ?string $end): string
    {
        if ($start && $end) {
            return Carbon::parse($start)->translatedFormat('F');
        }

        return match ($filter) {
            'bulanan' => Carbon::now()->translatedFormat('F'),
            'harian', 'mingguan', 'tahunan' => Carbon::now()->translatedFormat('F'),
            default => Carbon::now()->translatedFormat('F'),
        };
    }

    private function reportYearLabel(string $filter, ?string $start, ?string $end): string
    {
        if ($start && $end) {
            return Carbon::parse($start)->format('Y');
        }

        return Carbon::now()->format('Y');
    }
}
