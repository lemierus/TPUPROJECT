<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Concerns\WhatsAppNotifiable;
use App\Models\Makam;
use App\Models\Permohonan;
use App\Models\Jenazah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PermohonanController extends Controller
{
    use WhatsAppNotifiable;
    public function index()
    {
        $petugas = auth()->user();

        $pendingPermohonans = Permohonan::with(['user', 'jenazah.makam', 'makam'])
            ->where('tpu', $petugas->tpu)
            ->whereIn('status', $this->pendingStatuses())
            // Prioritaskan darurat yang menunggu konfirmasi/diproses di urutan atas,
            // lalu lanjutkan FIFO reguler berdasarkan waktu masuk paling lama.
            ->orderByRaw("
                CASE
                    WHEN jenis_permohonan = 'darurat' AND status IN ('menunggu_konfirmasi', 'diproses_darurat') THEN 0
                    ELSE 1
                END ASC
            ")
            ->orderBy('created_at', 'asc')
            ->get();

        $processedPermohonans = Permohonan::with(['user', 'jenazah.makam', 'makam'])
            ->where('tpu', $petugas->tpu)
            ->whereNotIn('status', $this->pendingStatuses())
            ->orderByDesc('updated_at')
            ->paginate(10);

        $pendingPermohonans->each(function (Permohonan $permohonan) {
            $permohonan->syncLinkedJenazahData();
            $permohonan->waiting_days = (int) floor($permohonan->created_at?->diffInDays(now()) ?? 0);
            $permohonan->is_overdue_queue = $permohonan->waiting_days > 3;
        });

        $processedPermohonans->each(function (Permohonan $permohonan) {
            $permohonan->syncLinkedJenazahData();
            return $permohonan;
        });

        $oldestPendingPermohonanId = $pendingPermohonans->first()?->id;

        $perpanjanganPerluDiingatkan = Permohonan::with(['user', 'jenazah.makam', 'makam'])
            ->where('tpu', $petugas->tpu)
            ->where('status', 'disetujui')
            ->where('jenis_permohonan', 'makam_baru')
            ->get()
            ->filter(function (Permohonan $permohonan) {
                $level = $permohonan->renewalAlertLevel();

                return in_array($level, ['soon', 'expired'], true);
            })
            ->sortBy(function (Permohonan $permohonan) {
                return $permohonan->renewalDueAt()?->timestamp ?? PHP_INT_MAX;
            })
            ->values();

        $stats = [
            'menunggu' => Permohonan::where('tpu', $petugas->tpu)
                ->whereIn('status', $this->pendingStatuses())
                ->count(),
            'disetujui' => Permohonan::where('tpu', $petugas->tpu)
                ->where('status', 'disetujui')
                ->count(),
            'ditolak' => Permohonan::where('tpu', $petugas->tpu)
                ->where('status', 'ditolak')
                ->count(),
            'total' => Permohonan::where('tpu', $petugas->tpu)->count(),
        ];

        return view('petugas.permohonan.index', compact(
            'pendingPermohonans',
            'processedPermohonans',
            'stats',
            'petugas',
            'perpanjanganPerluDiingatkan',
            'oldestPendingPermohonanId'
        ));
    }

    public function create()
    {
        return view('petugas.permohonan.create', [
            'petugas' => auth()->user(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis_permohonan' => ['required', Rule::in(['makam_baru'])],
            'jenazah_id' => ['nullable', 'exists:jenazah,id'],
            'nama_jenazah' => ['nullable', 'string', 'max:255'],
            'nik_jenazah' => ['nullable', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'tanggal_wafat' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
            'agama' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'nama_ahli_waris' => ['required', 'string', 'max:255'],
            'no_hp_ahli_waris' => ['required', 'string', 'max:30'],
            'hubungan_keluarga' => ['required', 'string', 'max:255'],
            'makam_id' => ['nullable', 'exists:makams,id'],
            'tahun_pemakaman' => ['nullable', 'string', 'max:255'],
            'tenggat_sewa_makam' => ['nullable', 'date'],
            'scan_ktp_ahli_waris' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'scan_kk' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'surat_kematian' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'catatan' => ['nullable', 'string'],
        ]);

        $validator->sometimes(['nama_jenazah', 'nik_jenazah', 'tanggal_wafat', 'jenis_kelamin', 'agama', 'tempat_lahir', 'tanggal_lahir', 'alamat'], 'required', function ($input) {
            return $input->jenis_permohonan === 'makam_baru';
        });

        $data = $validator->validate();

        $petugas = auth()->user();
        $selectedMakam = null;
        $selectedMakam = ! empty($data['makam_id'] ?? null)
            ? Makam::find($data['makam_id'])
            : null;

        $paths = [
            'scan_ktp_ahli_waris' => $request->file('scan_ktp_ahli_waris')->store('permohonan/ktp', 'public'),
            'scan_kk' => $request->file('scan_kk')->store('permohonan/kk', 'public'),
            'surat_kematian' => $request->file('surat_kematian')->store('permohonan/surat-kematian', 'public'),
        ];

        $permohonan = Permohonan::create(array_merge($data, [
            'user_id' => $petugas->id,
            'petugas_id' => $petugas->id,
            'tpu' => $petugas->tpu,
            'nama_pemohon' => $petugas->name,
            'scan_ktp_ahli_waris' => $paths['scan_ktp_ahli_waris'],
            'scan_kk' => $paths['scan_kk'],
            'surat_kematian' => $paths['surat_kematian'],
            'status' => 'menunggu',
            'jenazah_id' => null,
            'nama_jenazah' => $data['nama_jenazah'] ?? null,
            'nik_jenazah' => $data['nik_jenazah'] ?? null,
            'tempat_lahir' => $data['tempat_lahir'] ?? null,
            'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            'tanggal_wafat' => $data['tanggal_wafat'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
            'agama' => $data['agama'] ?? null,
            'alamat' => $data['alamat'] ?? null,
            'makam_id' => $selectedMakam?->id ?? null,
            'tahun_pemakaman' => $data['tahun_pemakaman'] ?? null,
            'tenggat_sewa_makam' => null,
        ]));

        return redirect()->route('petugas.permohonan.show', $permohonan)
            ->with('success', 'Permohonan berhasil dibuat.');
    }

    public function show(Permohonan $permohonan)
    {
        $this->authorizePermohonan($permohonan);
        $permohonan->loadMissing(['user', 'makam', 'jenazah.makam']);
        $permohonan->syncLinkedJenazahData();

        $oldestPendingPermohonanId = $this->oldestPendingPermohonanIdForTpu(auth()->user()->tpu);
        $isPendingQueue = in_array($permohonan->status, $this->pendingStatuses(), true);
        $isOutOfOrder = $isPendingQueue
            && $oldestPendingPermohonanId !== null
            && $permohonan->id !== $oldestPendingPermohonanId;
        $waitingDays = (int) floor($permohonan->created_at?->diffInDays(now()) ?? 0);
        $makamKosong = Makam::where('tpu', auth()->user()->tpu)
            ->where('status', 'kosong')
            ->orderBy('kode_makam')
            ->get();

        $renewalReminderWaUrl = $permohonan->renewalAlertLevel() === 'expired'
            ? $this->notifySewaReminder($permohonan)
            : null;

        return view('petugas.permohonan.show', compact(
            'permohonan',
            'renewalReminderWaUrl',
            'oldestPendingPermohonanId',
            'isOutOfOrder',
            'waitingDays',
            'makamKosong'
        ));
    }

    public function edit(Permohonan $permohonan)
    {
        $this->authorizePermohonan($permohonan);
        $permohonan->loadMissing(['user', 'makam', 'jenazah.makam']);
        $permohonan->syncLinkedJenazahData();

        return view('petugas.permohonan.edit', compact('permohonan'));
    }

    public function update(Request $request, Permohonan $permohonan)
    {
        $this->authorizePermohonan($permohonan);

        $validator = Validator::make($request->all(), [
            'jenis_permohonan' => ['required', Rule::in(['makam_baru', 'perpanjangan'])],
            'nama_jenazah' => ['nullable', 'string', 'max:255'],
            'nik_jenazah' => ['nullable', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'tanggal_wafat' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
            'agama' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'nama_ahli_waris' => ['nullable', 'string', 'max:255'],
            'no_hp_ahli_waris' => ['nullable', 'string', 'max:30'],
            'hubungan_keluarga' => ['nullable', 'string', 'max:255'],
            'makam_id' => ['nullable', 'exists:makams,id'],
            'tahun_pemakaman' => ['nullable', 'string', 'max:255'],
            'tenggat_sewa_makam' => ['nullable', 'date'],
            'scan_ktp_ahli_waris' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'scan_kk' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'surat_kematian' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'catatan' => ['nullable', 'string'],
        ]);

        $validator->sometimes(['nama_jenazah', 'nik_jenazah', 'tanggal_wafat', 'jenis_kelamin', 'agama', 'tempat_lahir', 'tanggal_lahir', 'alamat'], 'required', function ($input) {
            return $input->jenis_permohonan === 'makam_baru';
        });

        $validator->sometimes(['tenggat_sewa_makam'], 'required', function ($input) {
            return $input->jenis_permohonan === 'perpanjangan';
        });

        $data = $validator->validate();
        $selectedMakam = ! empty($data['makam_id'] ?? null) ? Makam::find($data['makam_id']) : null;

        if ($data['jenis_permohonan'] === 'perpanjangan') {
            $data['jenazah_id'] = $permohonan->jenazah_id;
            $data['makam_id'] = $permohonan->makam_id;
            $data['tahun_pemakaman'] = $permohonan->tahun_pemakaman;
        }

            foreach (['scan_ktp_ahli_waris', 'scan_kk', 'surat_kematian'] as $fileKey) {
                if ($request->hasFile($fileKey)) {
                    $data[$fileKey] = $request->file($fileKey)->store('permohonan/' . $fileKey, 'public');
                }
        }

        $permohonan->fill(array_merge($data, [
            'jenazah_id' => $data['jenazah_id'] ?? $permohonan->jenazah_id,
            'nama_jenazah' => $data['nama_jenazah'] ?? null,
            'nik_jenazah' => $data['nik_jenazah'] ?? null,
            'tempat_lahir' => $data['tempat_lahir'] ?? null,
            'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            'tanggal_wafat' => $data['tanggal_wafat'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
            'agama' => $data['agama'] ?? null,
            'alamat' => $data['alamat'] ?? null,
            'makam_id' => $selectedMakam?->id ?? $permohonan->makam_id,
            'tahun_pemakaman' => $data['tahun_pemakaman'] ?? $permohonan->tahun_pemakaman,
            'tenggat_sewa_makam' => $data['tenggat_sewa_makam'] ?? $permohonan->tenggat_sewa_makam ?? null,
        ]));
        $permohonan->save();
        $permohonan->syncLinkedJenazahData();

        if ($permohonan->jenis_permohonan === 'perpanjangan' && $permohonan->tenggat_sewa_makam) {
            $permohonan->loadMissing('jenazah');

            if ($permohonan->jenazah) {
                $permohonan->jenazah->update([
                    'tenggat_sewa_makam' => $permohonan->tenggat_sewa_makam,
                ]);
            }
        }

        return redirect()->route('petugas.permohonan.show', $permohonan)
            ->with('success', 'Data permohonan berhasil diperbarui.');
    }

    public function prosesDarurat(Permohonan $permohonan)
    {
        $this->authorizePermohonan($permohonan);
        abort_unless($permohonan->isDarurat(), 404);
        abort_unless($permohonan->status === Permohonan::STATUS_MENUNGGU_KONFIRMASI, 422, 'Permohonan darurat ini tidak dapat diproses.');

        $permohonan->update([
            'status' => Permohonan::STATUS_DIPROSES_DARURAT,
        ]);

        return redirect()->route('petugas.permohonan.show', $permohonan)
            ->with('success', 'Permohonan darurat sedang diproses.');
    }

    public function selesaikanPemakaman(Request $request, Permohonan $permohonan)
    {
        $this->authorizePermohonan($permohonan);
        abort_unless($permohonan->isDarurat(), 404);
        abort_unless($permohonan->status === Permohonan::STATUS_DIPROSES_DARURAT, 422, 'Permohonan darurat ini belum berada pada tahap penyelesaian.');

        $data = $request->validate([
            'makam_id' => ['required', 'exists:makams,id'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'makam_id.required' => 'Silakan pilih makam kosong untuk penyelesaian pemakaman darurat.',
            'makam_id.exists' => 'Makam yang dipilih tidak ditemukan.',
        ]);

        DB::transaction(function () use ($permohonan, $data) {
            $makam = Makam::where('id', $data['makam_id'])
                ->where('tpu', auth()->user()->tpu)
                ->firstOrFail();

            if ($makam->status !== 'kosong') {
                throw ValidationException::withMessages([
                    'makam_id' => 'Makam yang dipilih sudah terisi. Pilih makam kosong lainnya.',
                ]);
            }

            $permohonan->fill([
                'makam_id' => $makam->id,
                'status' => Permohonan::STATUS_ADMINISTRASI_BELUM_LENGKAP,
                'catatan' => $data['catatan'] ?? $permohonan->catatan,
            ]);
            $permohonan->save();

            $jenazah = $permohonan->persistJenazahRecord();
            $jenazah->update([
                'makam_id' => $makam->id,
            ]);

            $makam->update([
                'status' => 'terisi',
            ]);
        });

        return redirect()->route('petugas.permohonan.show', $permohonan)
            ->with('success', 'Pemakaman darurat selesai. Ahli waris sekarang perlu melengkapi administrasi.');
    }

    public function verifikasiDokumen(Request $request, Permohonan $permohonan)
    {
        $this->authorizePermohonan($permohonan);
        abort_unless($permohonan->status === Permohonan::STATUS_MENUNGGU_VERIFIKASI_DOKUMEN, 422, 'Dokumen belum siap diverifikasi.');

        $data = $request->validate([
            'aksi' => ['required', Rule::in(['setujui', 'perbaikan'])],
            'tenggat_sewa_makam' => ['nullable', 'date', 'required_if:aksi,setujui'],
            'catatan_revisi' => ['nullable', 'string', 'required_if:aksi,perbaikan'],
        ], [
            'aksi.required' => 'Aksi verifikasi wajib dipilih.',
            'tenggat_sewa_makam.required_if' => 'Tenggat sewa makam wajib diisi saat menyetujui dokumen.',
            'catatan_revisi.required_if' => 'Catatan revisi wajib diisi jika dokumen perlu perbaikan.',
        ]);

        DB::transaction(function () use ($permohonan, $data) {
            if ($data['aksi'] === 'setujui') {
                $permohonan->update([
                    'status' => Permohonan::STATUS_SELESAI,
                    'catatan_revisi' => null,
                    'tenggat_sewa_makam' => $data['tenggat_sewa_makam'],
                    'approved_at' => $permohonan->approved_at ?? now(),
                ]);

                $permohonan->loadMissing('jenazah');
                if ($permohonan->jenazah) {
                    $permohonan->jenazah->update([
                        'tenggat_sewa_makam' => $data['tenggat_sewa_makam'],
                    ]);
                }
            } else {
                $permohonan->update([
                    'status' => Permohonan::STATUS_PERLU_PERBAIKAN_DOKUMEN,
                    'catatan_revisi' => $data['catatan_revisi'],
                ]);
            }
        });

        return redirect()->route('petugas.permohonan.show', $permohonan)
            ->with('success', $data['aksi'] === 'setujui'
                ? 'Dokumen permohonan darurat berhasil diverifikasi.'
                : 'Permohonan dikembalikan ke ahli waris untuk perbaikan dokumen.');
    }

    public function approve(Request $request, Permohonan $permohonan)
    {
        $this->authorizePermohonan($permohonan);
        $wasOutOfOrder = $this->isProcessingOutOfOrder($permohonan);

        $request->validate([
            'tenggat_sewa_makam' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use ($request, $permohonan) {
                $permohonan->status = 'disetujui';
                $permohonan->approved_at = now();
                if ($request->filled('catatan')) {
                    $permohonan->catatan = $request->catatan;
                }
                if ($request->filled('tenggat_sewa_makam')) {
                    $permohonan->tenggat_sewa_makam = $request->tenggat_sewa_makam;
                }
                $permohonan->save();

                \Log::info('Permohonan disetujui', [
                    'id' => $permohonan->id,
                    'jenis_permohonan' => $permohonan->jenis_permohonan,
                    'jenazah_id' => $permohonan->jenazah_id,
                    'nama_jenazah' => $permohonan->nama_jenazah,
                    'nik_jenazah' => $permohonan->nik_jenazah,
                ]);

                if ($permohonan->hasCompleteJenazahData() && ! $permohonan->jenazah_id) {
                    \Log::info('Memulai create jenazah dari permohonan', ['permohonan_id' => $permohonan->id]);
                    $permohonan->persistJenazahRecord();
                    \Log::info('Jenazah berhasil dibuat', ['jenazah_id' => $permohonan->jenazah_id]);
                }

                if ($permohonan->tenggat_sewa_makam) {
                    $permohonan->loadMissing('jenazah');
                    if ($permohonan->jenazah) {
                        $permohonan->jenazah->update([
                            'tenggat_sewa_makam' => $permohonan->tenggat_sewa_makam,
                        ]);
                    }
                }
            });
        } catch (ValidationException $e) {
            \Log::warning('ValidationException saat approve', ['errors' => $e->errors(), 'permohonan_id' => $permohonan->id]);
            return redirect()->route('petugas.permohonan.show', $permohonan)
                ->withErrors($e->errors())
                ->with('error', collect($e->errors())->flatten()->first() ?? 'Gagal menyetujui permohonan.');
        } catch (\Exception $e) {
            \Log::error('Error saat approve permohonan', ['message' => $e->getMessage(), 'permohonan_id' => $permohonan->id]);
            return redirect()->route('petugas.permohonan.show', $permohonan)
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        $waUrl = $this->notifyPermohonanStatus($permohonan);

        $successMsg = 'Permohonan berhasil disetujui dan data jenazah tersimpan.';
        if ($waUrl) {
            $successMsg .= ' <a href="' . e($waUrl) . '" target="_blank" class="whatsapp-link-inline"><i class="bi bi-whatsapp"></i> Kirim notifikasi WhatsApp ke ahli waris</a>';
        }
        if ($wasOutOfOrder) {
            $successMsg = '<strong>Catatan FIFO:</strong> permohonan ini diproses tidak dari urutan paling awal. ' . $successMsg;
        }

        return redirect()->route('petugas.permohonan')
            ->with('success', $successMsg);
    }

    public function reject(Request $request, Permohonan $permohonan)
    {
        $this->authorizePermohonan($permohonan);
        $wasOutOfOrder = $this->isProcessingOutOfOrder($permohonan);

        $request->validate([
            'catatan' => ['required', 'string', 'max:1000'],
        ]);

        $permohonan->update([
            'status' => 'ditolak',
            'catatan' => $request->catatan,
        ]);

        $permohonan->refresh();

        $waUrl = $this->notifyPermohonanStatus($permohonan);

        $successMsg = 'Permohonan berhasil ditolak.';
        if ($waUrl) {
            $successMsg .= ' <a href="' . e($waUrl) . '" target="_blank" class="whatsapp-link-inline"><i class="bi bi-whatsapp"></i> Kirim notifikasi WhatsApp ke ahli waris</a>';
        }
        if ($wasOutOfOrder) {
            $successMsg = '<strong>Catatan FIFO:</strong> permohonan ini diproses tidak dari urutan paling awal. ' . $successMsg;
        }

        return redirect()->route('petugas.permohonan')
            ->with('success', $successMsg);
    }

    private function authorizePermohonan(Permohonan $permohonan): void
    {
        abort_unless(
            $permohonan->tpu === auth()->user()->tpu,
            403,
            'Anda tidak memiliki akses ke permohonan ini.'
        );
    }

    private function pendingStatuses(): array
    {
        return [
            Permohonan::STATUS_PENDING,
            Permohonan::STATUS_MENUNGGU,
            Permohonan::STATUS_MENUNGGU_KONFIRMASI,
            Permohonan::STATUS_DIPROSES_DARURAT,
            Permohonan::STATUS_MENUNGGU_VERIFIKASI_DOKUMEN,
            Permohonan::STATUS_PERLU_PERBAIKAN_DOKUMEN,
        ];
    }

    private function oldestPendingPermohonanIdForTpu(string $tpu): ?int
    {
        return Permohonan::where('tpu', $tpu)
            ->whereIn('status', $this->pendingStatuses())
            ->orderBy('created_at', 'asc')
            ->value('id');
    }

    private function isProcessingOutOfOrder(Permohonan $permohonan): bool
    {
        if (! in_array($permohonan->status, $this->pendingStatuses(), true)) {
            return false;
        }

        $oldestPendingId = $this->oldestPendingPermohonanIdForTpu($permohonan->tpu);

        return $oldestPendingId !== null && $oldestPendingId !== $permohonan->id;
    }

    private function eligibleRenewalJenazahs(string $tpu)
    {
        $query = Permohonan::with(['jenazah', 'makam', 'user'])
            ->where('tpu', $tpu)
            ->where('status', 'disetujui')
            ->where('jenis_permohonan', 'makam_baru')
            ->whereNotNull('jenazah_id');

        if (Schema::hasColumn('permohonans', 'approved_at')) {
            $query->orderByDesc('approved_at');
        } else {
            $query->orderByDesc('updated_at');
        }

        return $query->get()
            ->filter(function (Permohonan $permohonan) {
                return $permohonan->jenazah && $permohonan->makam;
            })
            ->values();
    }

    private function resolveRenewalSource($jenazahId, ?string $namaJenazah, string $tpu): ?Permohonan
    {
        $query = Permohonan::with(['jenazah', 'makam', 'user'])
            ->where('tpu', $tpu)
            ->where('status', 'disetujui')
            ->where('jenis_permohonan', 'makam_baru')
            ->whereNotNull('jenazah_id');

        if ($jenazahId) {
            $query->where('jenazah_id', $jenazahId);
        } elseif ($namaJenazah) {
            $query->where(function ($subQuery) use ($namaJenazah) {
                $subQuery->where('nama_jenazah', $namaJenazah)
                    ->orWhereHas('jenazah', function ($jenazahQuery) use ($namaJenazah) {
                        $jenazahQuery->where('nama', $namaJenazah);
                    });
            });
        }

        return $query->first();
    }
}
