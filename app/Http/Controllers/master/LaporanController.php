<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Jenazah;
use App\Models\Laporan;
use App\Models\Makam;
use App\Models\Permohonan;
use App\Models\User;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
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

        if (auth()->user()?->isKepala() || auth()->user()?->isKdlh()) {
            $report = $this->buildKepalaReport($filter, $start, $end);

            // === PAGINATION: 10 data per halaman untuk tabel laporan gabungan ===
            // Dilakukan di akhir (setelah semua statistik dihitung dari data utuh)
            // supaya angka kartu statistik tidak ikut terpotong oleh pagination.
            $report['reportRows'] = $this->paginateRows($report['reportRows']);

            return view('pages.master.laporan', array_merge($report, [
                'routePrefix' => auth()->user()?->isKdlh() ? 'kdlh' : 'kepala',
                'isPetugasReport' => false,
                'isKepalaReport' => true,
                'wordRoute' => null,
                'filter' => $filter,
                'start' => $start,
                'end' => $end,
            ]));
        }

        if (auth()->user()?->isPetugas()) {
            $report = $this->buildPetugasReport($filter, $start, $end);

            // === PAGINATION: 10 data per halaman untuk tabel laporan gabungan ===
            $report['reportRows'] = $this->paginateRows($report['reportRows']);

            return view('pages.master.laporan', array_merge($report, [
                'routePrefix' => 'petugas',
                'isPetugasReport' => true,
                'isKepalaReport' => false,
                'wordRoute' => route('petugas.master.laporan.word', $request->query()),
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
            // Untuk role selain petugas/kepala/kdlh, tabel yang dipakai adalah $data,
            // bukan $reportRows, sehingga cukup gunakan paginate() bawaan Eloquent.
            'data' => $this->paginateRows($data),
            'total' => $data->count(),
            'laki' => $data->whereIn('jenis_kelamin', ['L', 'Laki-laki'])->count(),
            'perempuan' => $data->whereIn('jenis_kelamin', ['P', 'Perempuan'])->count(),
            'totalMakamTerisi' => $data->whereNotNull('makam_id')->count(),
            'isPetugasReport' => false,
            'isKepalaReport' => false,
            'selectedTpu' => $selectedTpu,
            'tpuOptions' => $tpuOptions,
            'wordRoute' => null,
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
        // Halaman print/cetak TIDAK dipaginate — harus menampilkan seluruh data
        // agar hasil cetak lengkap, bukan hanya 10 data pertama.
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
        $filter = $request->filter ?? 'bulanan';
        $start = $request->start;
        $end = $request->end;

        abort_unless(auth()->user()?->isPetugas(), 403);
        $report = $this->buildWordReport($filter, $start, $end, $request->tpu);

        $prefix = 'laporan-petugas';
        $scopeSlug = Str::slug($report['scopeLabel'] ?? auth()->user()->tpu ?? 'semua-tpu');
        $filename = $prefix . '-' . $scopeSlug . '-' . now()->format('Ymd-His') . '.docx';

        $templatePath = storage_path('app/template-laporan-bulanan.docx');
        abort_unless(file_exists($templatePath), 500, 'Template laporan Word tidak ditemukan.');

        $outputPath = $this->createWordReportFromTemplate($templatePath, $report);

        return response()->download($outputPath, $filename)->deleteFileAfterSend(true);
    }

    public function excel(Request $request)
    {
        $filter = $request->filter ?? 'harian';
        $start = $request->start;
        $end = $request->end;

        abort_unless(auth()->user()?->isPetugas() || auth()->user()?->isKepala(), 403);
        // Export Excel TIDAK dipaginate — harus menampilkan seluruh data.
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

    /**
     * Memotong sebuah Collection menjadi halaman-halaman berisi 10 data,
     * lalu membungkusnya dengan LengthAwarePaginator supaya bisa dipakai
     * seperti hasil paginate() biasa di view (links(), total(), dst).
     *
     * Dipakai khusus untuk halaman index (tampilan web), TIDAK dipakai
     * untuk print/word/excel karena laporan cetak/unduh harus lengkap.
     */
    private function paginateRows(Collection $rows, int $perPage = 10, string $pageName = 'page'): LengthAwarePaginator
    {
        $currentPage = Paginator::resolveCurrentPage($pageName);
        $currentPage = $currentPage && $currentPage > 0 ? (int) $currentPage : 1;

        $items = $rows->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $rows->count(),
            $perPage,
            $currentPage,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
                'query' => request()->query(),
            ]
        );
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

    private function buildKepalaReport(string $filter, ?string $start, ?string $end): array
    {
        $permohonanQuery = Permohonan::with(['makam', 'jenazah', 'user']);
        $this->applyDateFilter($permohonanQuery, $filter, $start, $end, 'created_at');
        $permohonans = $permohonanQuery->latest('created_at')->get();

        $jenazahQuery = Jenazah::with('makam');
        $this->applyDateFilter($jenazahQuery, $filter, $start, $end, 'tanggal_wafat');
        $jenazahs = $jenazahQuery->latest('created_at')->get();

        $rows = $this->buildReportRows($permohonans, $jenazahs)->sortByDesc('tanggal_sort')->values();

        return [
            'isPetugasReport' => false,
            'isKepalaReport' => true,
            'scopeLabel' => 'Semua TPU',
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
        $scopeTpu = match (true) {
            auth()->user()?->isPetugas() => auth()->user()->tpu,
            auth()->user()?->isKdlh() => null,
            auth()->user()?->isAdmin() => filled($selectedTpu) ? $selectedTpu : null,
            default => null,
        };
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
        $tpuOptions = User::tpuOptions();

        $approvedMakamBaru = $permohonans->where('jenis_permohonan', 'makam_baru')->where('status', 'disetujui')->count();
        $approvedPerpanjangan = $permohonans->where('jenis_permohonan', 'perpanjangan')->where('status', 'disetujui')->count();
        $perawatan = 0;
        $makamAktif = $jenazahs->whereNotNull('makam_id')->count();
        $berakhirSewa = $jenazahs->filter(function (Jenazah $item) {
            $due = $item->renewalDueAt();
            return $due && $due->isPast();
        })->count();

        $ringkasanRows = [
            ['label' => 'Jumlah pemakaman baru', 'value' => $approvedMakamBaru . ' makam'],
            ['label' => 'Jumlah perpanjangan makam', 'value' => $approvedPerpanjangan . ' makam'],
            ['label' => 'Jumlah perawatan makam', 'value' => $perawatan . ' makam'],
            ['label' => 'Jumlah makam aktif', 'value' => $makamAktif . ' makam'],
            ['label' => 'Jumlah makam yang berakhir masa sewa', 'value' => $berakhirSewa . ' makam'],
        ];

        $rekapPelayananRows = collect($tpuOptions)->map(function (string $tpu) use ($filter, $start, $end) {
            $permohonanQuery = Permohonan::query()->with(['jenazah', 'makam', 'user'])->where('tpu', $tpu);
            $this->applyDateFilter($permohonanQuery, $filter, $start, $end, 'created_at');
            $permohonan = $permohonanQuery->get();

            return [
                'tpu' => $tpu,
                'pemakaman_baru' => $permohonan->where('jenis_permohonan', 'makam_baru')->where('status', 'disetujui')->count(),
                'perpanjangan' => $permohonan->where('jenis_permohonan', 'perpanjangan')->where('status', 'disetujui')->count(),
                'menunggu' => $permohonan->whereIn('status', ['menunggu', 'pending'])->count(),
                'disetujui' => $permohonan->where('status', 'disetujui')->count(),
                'ditolak' => $permohonan->where('status', 'ditolak')->count(),
                'total' => $permohonan->count(),
            ];
        })->values();

        $dataPemakamanRows = collect($tpuOptions)->map(function (string $tpu) use ($filter, $start, $end) {
            $jenazahQuery = Jenazah::query()->with('makam')->where('tpu', $tpu);
            $this->applyDateFilter($jenazahQuery, $filter, $start, $end, 'tanggal_wafat');
            $jenazah = $jenazahQuery->get();

            $permohonanQuery = Permohonan::query()->where('tpu', $tpu);
            $this->applyDateFilter($permohonanQuery, $filter, $start, $end, 'created_at');
            $permohonanCount = $permohonanQuery->count();
            $totalMakam = Makam::where('tpu', $tpu)->count();
            $makamTerisi = $jenazah->whereNotNull('makam_id')->count();

            return [
                'tpu' => $tpu,
                'total_jenazah' => $jenazah->count(),
                'total_makam' => $makamTerisi,
                'total_makam_kosong' => max(0, $totalMakam - $makamTerisi),
                'laki_laki' => $jenazah->whereIn('jenis_kelamin', ['L', 'Laki-laki'])->count(),
                'perempuan' => $jenazah->whereIn('jenis_kelamin', ['P', 'Perempuan'])->count(),
                'permohonan' => $permohonanCount,
            ];
        })->values();

        $statistikRows = $rows
            ->groupBy(fn ($row) => trim(($row['blok'] ?? '-') . ' / ' . ($row['zona'] ?? '-')))
            ->map(fn ($items, $key) => [
                'blok_zona' => str_replace('/', ' / ', $key),
                'jumlah' => $items->count(),
            ])
            ->sortByDesc('jumlah')
            ->values();

        $statistikJenisKelaminRows = collect([
            ['label' => 'Laki-Laki', 'jumlah' => $jenazahs->whereIn('jenis_kelamin', ['L', 'Laki-laki', 'Laki Laki'])->count()],
            ['label' => 'Perempuan', 'jumlah' => $jenazahs->whereIn('jenis_kelamin', ['P', 'Perempuan'])->count()],
        ])->push([
            'label' => 'Total',
            'jumlah' => $jenazahs->count(),
        ]);

        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
        $statistikAgamaRows = collect($religions)->map(function (string $religion) use ($jenazahs) {
            return [
                'label' => $religion,
                'jumlah' => $jenazahs->filter(function (Jenazah $item) use ($religion) {
                    return $this->normalizeReligion($item->agama) === $religion;
                })->count(),
            ];
        })->push([
            'label' => 'Total',
            'jumlah' => $jenazahs->count(),
        ]);

        return [
            'reportRows' => $rows,
            'permohonansReport' => $permohonans,
            'jenazahsReport' => $jenazahs,
            'scopeLabel' => $scopeLabel,
            'periodLabel' => $this->reportPeriodLabel($filter, $start, $end),
            'monthLabel' => $this->reportMonthLabel($filter, $start, $end),
            'yearLabel' => $this->reportYearLabel($filter, $start, $end),
            'ringkasanRows' => $ringkasanRows,
            'rekapPelayananRows' => $rekapPelayananRows,
            'dataPemakamanRows' => $dataPemakamanRows,
            'statistikRows' => $statistikRows,
            'statistikJenisKelaminRows' => $statistikJenisKelaminRows,
            'statistikAgamaRows' => $statistikAgamaRows,
            'reportDate' => now()->translatedFormat('d F Y'),
            'totalPemakamanBaru' => $approvedMakamBaru,
            'totalPerpanjangan' => $approvedPerpanjangan,
            'totalPerawatan' => $perawatan,
            'totalMakamAktif' => $makamAktif,
            'totalMakamBerakhirSewa' => $berakhirSewa,
            'permohonanMenunggu' => $permohonans->whereIn('status', ['menunggu', 'pending'])->count(),
            'permohonanDisetujui' => $permohonans->where('status', 'disetujui')->count(),
            'permohonanDitolak' => $permohonans->where('status', 'ditolak')->count(),
            'totalPermohonan' => $permohonans->count(),
            'totalJenazah' => $jenazahs->count(),
        ];
    }

    private function createWordReportFromTemplate(string $templatePath, array $report): string
    {
        $sourceTemp = tempnam(sys_get_temp_dir(), 'tpu_laporan_source_');
        abort_unless($sourceTemp !== false, 500, 'Gagal membuat file sementara laporan.');

        $sourceZipPath = $sourceTemp . '.zip';
        @unlink($sourceTemp);
        abort_unless(copy($templatePath, $sourceZipPath), 500, 'Gagal menyalin template laporan.');

        $extractDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('tpu_laporan_extract_', true);
        mkdir($extractDir, 0777, true);

        $this->runPowerShell(
            'Expand-Archive -LiteralPath ' . $this->psQuote($sourceZipPath) .
            ' -DestinationPath ' . $this->psQuote($extractDir) . ' -Force'
        );

        $documentPath = $extractDir . DIRECTORY_SEPARATOR . 'word' . DIRECTORY_SEPARATOR . 'document.xml';
        abort_unless(file_exists($documentPath), 500, 'Isi template laporan tidak ditemukan.');

        $xml = file_get_contents($documentPath);
        abort_unless($xml !== false, 500, 'Isi template laporan tidak ditemukan.');

        $xml = str_replace(
            'Bulan: ................. Tahun: .................',
            'Bulan: ' . $report['monthLabel'] . ' Tahun: ' . $report['yearLabel'],
            $xml
        );
        $xml = str_replace(
            'Padang, ................. 20....',
            'Padang, ' . $report['reportDate'],
            $xml
        );

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;
        libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($xml);
        libxml_clear_errors();
        abort_unless($loaded, 500, 'Template laporan Word tidak valid.');

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $tables = $xpath->query('//w:tbl');
        abort_unless($tables !== false && $tables->length >= 8, 500, 'Struktur tabel template laporan tidak sesuai.');

        $this->fillTableCell($xpath, $tables->item(0), 1, 1, 'UPT Tempat Pemakaman Umum Kota Padang');
        $this->fillTableCell($xpath, $tables->item(0), 2, 1, $report['periodLabel']);
        $this->fillTableCell($xpath, $tables->item(0), 3, 1, $report['reportDate']);
        $this->fillTableCell($xpath, $tables->item(0), 4, 1, 'Kepala UPT TPU Kota Padang');

        $ringkasanMap = $report['dataPemakamanRows']->keyBy('tpu');
        $ringkasanTargets = [
            'TPU Tunggul Hitam',
            'TPU Air Dingin',
            'TPU Bungus Teluk Kabung',
        ];

        foreach ($ringkasanTargets as $index => $tpu) {
            $row = $ringkasanMap->get($tpu, [
                'tpu' => $tpu,
                'total_jenazah' => 0,
                'total_makam' => 0,
            ]);

            $this->fillTableCell($xpath, $tables->item(1), $index + 1, 1, (string) ($index + 1));
            $this->fillTableCell($xpath, $tables->item(1), $index + 1, 2, $tpu);
            $this->fillTableCell($xpath, $tables->item(1), $index + 1, 3, (string) $row['total_jenazah']);
            $this->fillTableCell($xpath, $tables->item(1), $index + 1, 4, (string) $row['total_makam']);
            $this->fillTableCell($xpath, $tables->item(1), $index + 1, 5, (string) $row['total_makam_kosong']);
        }

        $this->fillTableCell($xpath, $tables->item(1), 4, 0, 'Total');
        $this->fillTableCell($xpath, $tables->item(1), 4, 1, '');
        $this->fillTableCell($xpath, $tables->item(1), 4, 2, (string) ($report['dataPemakamanRows']->sum('total_jenazah')));
        $this->fillTableCell($xpath, $tables->item(1), 4, 3, (string) ($report['dataPemakamanRows']->sum('total_makam')));
        $this->fillTableCell($xpath, $tables->item(1), 4, 4, (string) ($report['dataPemakamanRows']->sum('total_makam_kosong')));

        $this->fillTableCell($xpath, $tables->item(2), 1, 2, (string) $report['totalPemakamanBaru']);
        $this->fillTableCell($xpath, $tables->item(2), 2, 2, (string) $report['totalPerawatan']);
        $this->fillTableCell($xpath, $tables->item(2), 3, 2, (string) $report['permohonanDisetujui']);
        $this->fillTableCell($xpath, $tables->item(2), 4, 2, (string) $report['permohonanDitolak']);
        $this->fillTableCell($xpath, $tables->item(2), 5, 2, (string) $report['permohonanMenunggu']);
        $this->fillTableCell($xpath, $tables->item(2), 6, 2, (string) $report['totalPermohonan']);

        $this->populateDetailTables($dom, $xpath, $tables, $report['jenazahsReport']);

        $jenisKelaminRows = $report['statistikJenisKelaminRows']->values();
        $this->fillTableCell($xpath, $tables->item(6), 1, 1, (string) ($jenisKelaminRows[0]['jumlah'] ?? 0));
        $this->fillTableCell($xpath, $tables->item(6), 2, 1, (string) ($jenisKelaminRows[1]['jumlah'] ?? 0));
        $this->fillTableCell($xpath, $tables->item(6), 3, 1, (string) ($jenisKelaminRows[2]['jumlah'] ?? 0));

        $agamaRows = $report['statistikAgamaRows']->values();
        for ($i = 0; $i < 6; $i++) {
            $this->fillTableCell($xpath, $tables->item(7), $i + 1, 1, (string) ($agamaRows[$i]['jumlah'] ?? 0));
        }
        $this->fillTableCell($xpath, $tables->item(7), 7, 1, (string) ($agamaRows[6]['jumlah'] ?? 0));

        abort_unless(file_put_contents($documentPath, $dom->saveXML()) !== false, 500, 'Gagal menulis isi laporan.');

        $resultTemp = tempnam(sys_get_temp_dir(), 'tpu_laporan_result_');
        abort_unless($resultTemp !== false, 500, 'Gagal membuat arsip laporan.');
        $resultZipPath = $resultTemp . '.zip';
        @unlink($resultTemp);

        $this->runPowerShell(
            'Compress-Archive -Path ' . $this->psQuote($extractDir . DIRECTORY_SEPARATOR . '*') .
            ' -DestinationPath ' . $this->psQuote($resultZipPath) . ' -Force'
        );

        $outputTemp = tempnam(sys_get_temp_dir(), 'tpu_laporan_');
        abort_unless($outputTemp !== false, 500, 'Gagal membuat file output laporan.');
        $outputPath = $outputTemp . '.docx';
        @unlink($outputTemp);
        abort_unless(rename($resultZipPath, $outputPath), 500, 'Gagal menyiapkan file laporan akhir.');

        $this->deleteDirectoryRecursive($extractDir);
        @unlink($sourceZipPath);

        return $outputPath;
    }

    private function populateDetailTables(DOMDocument $dom, DOMXPath $xpath, $tables, Collection $jenazahs): void
    {
        $byTpu = $jenazahs->groupBy(fn (Jenazah $item) => $item->tpu ?: 'TPU Tidak Diketahui');
        $targetTpus = ['TPU Tunggul Hitam', 'TPU Air Dingin', 'TPU Bungus Teluk Kabung'];

        foreach ($targetTpus as $tableOffset => $tpu) {
            $table = $tables->item(3 + $tableOffset);
            if (! $table) {
                continue;
            }

            $rows = $byTpu->get($tpu, collect())->values();
            $tableRows = $xpath->query('./w:tr', $table);
            if (! $tableRows || $tableRows->length < 2) {
                continue;
            }

            $headerRow = $tableRows->item(0);
            $templateRow = $tableRows->item(1);

            while ($tableRows->length > 2) {
                $table->removeChild($tableRows->item(2));
                $tableRows = $xpath->query('./w:tr', $table);
            }

            if ($rows->isEmpty()) {
                $this->fillTableCell($xpath, $table, 1, 0, '-');
                $this->fillTableCell($xpath, $table, 1, 1, '-');
                $this->fillTableCell($xpath, $table, 1, 2, '-');
                $this->fillTableCell($xpath, $table, 1, 3, '-');
                $this->fillTableCell($xpath, $table, 1, 4, '-');
                continue;
            }

            foreach ($rows as $index => $item) {
                $rowNode = $index === 0 ? $templateRow : $templateRow->cloneNode(true);
                if ($index > 0) {
                    $table->appendChild($rowNode);
                }

                $tanggalPemakaman = $this->formatDate($item->tanggal_wafat ?? $item->created_at);
                $lokasiMakam = collect([
                    $item->kode_makam ?: null,
                    $item->blok ?: null,
                    $item->zona ?: null,
                    $item->nomor_makam ?: null,
                ])->filter()->implode(' / ');

                $this->fillRowCells($xpath, $rowNode, [
                    (string) ($index + 1),
                    $item->nama ?: '-',
                    $tanggalPemakaman,
                    $item->blok ?: '-',
                    $lokasiMakam ?: '-',
                ]);
            }
        }
    }

    private function fillTableCell(DOMXPath $xpath, $table, int $rowIndex, int $cellIndex, string $text): void
    {
        if (! $table instanceof DOMElement) {
            return;
        }

        $rows = $xpath->query('./w:tr', $table);
        if (! $rows || ! $rows->item($rowIndex)) {
            return;
        }

        $this->fillRowCells($xpath, $rows->item($rowIndex), [], $cellIndex, $text);
    }

    private function fillRowCells(DOMXPath $xpath, DOMElement $row, array $values, ?int $cellIndex = null, ?string $text = null): void
    {
        $cells = $xpath->query('./w:tc', $row);
        if (! $cells) {
            return;
        }

        if ($cellIndex !== null) {
            $cell = $cells->item($cellIndex);
            if ($cell) {
                $this->replaceCellText($cell, $text ?? '');
            }
            return;
        }

        foreach ($values as $index => $value) {
            $cell = $cells->item($index);
            if ($cell) {
                $this->replaceCellText($cell, (string) $value);
            }
        }
    }

    private function replaceCellText(DOMElement $cell, string $text): void
    {
        $paragraph = null;
        foreach ($cell->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'p') {
                $paragraph = $child;
                break;
            }
        }

        if (! $paragraph) {
            return;
        }

        $xpath = new DOMXPath($cell->ownerDocument);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $texts = $xpath->query('.//w:t', $paragraph);
        if ($texts && $texts->length > 0) {
            $texts->item(0)->nodeValue = $text;
            for ($i = 1; $i < $texts->length; $i++) {
                $texts->item($i)->nodeValue = '';
            }
            return;
        }

        $run = $cell->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:r');
        $textNode = $cell->ownerDocument->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:t');
        if ($text !== trim($text)) {
            $textNode->setAttribute('xml:space', 'preserve');
        }
        $textNode->nodeValue = $text;
        $run->appendChild($textNode);
        $paragraph->appendChild($run);
    }

    private function normalizeReligion(?string $value): string
    {
        $normalized = Str::of((string) $value)->trim()->lower();

        return match ((string) $normalized) {
            'islam', 'muslim' => 'Islam',
            'kristen', 'protestan' => 'Kristen',
            'katolik' => 'Katolik',
            'hindu' => 'Hindu',
            'buddha' => 'Buddha',
            'konghucu', 'confucianism' => 'Konghucu',
            default => '',
        };
    }

    private function runPowerShell(string $script): void
    {
        $command = 'powershell.exe -NoProfile -ExecutionPolicy Bypass -Command ' . escapeshellarg($script);
        exec($command, $output, $code);

        abort_unless($code === 0, 500, 'Gagal memproses file Word template.');
    }

    private function psQuote(string $value): string
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }

    private function deleteDirectoryRecursive(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = array_diff(scandir($directory) ?: [], ['.', '..']);
        foreach ($items as $item) {
            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectoryRecursive($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($directory);
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