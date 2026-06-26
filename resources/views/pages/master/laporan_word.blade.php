<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bulanan TPU {{ $scopeLabel ?? 'Semua TPU' }}</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 12pt;
            color: #111827;
            margin: 28px 36px;
            line-height: 1.45;
        }

        .center { text-align: center; }
        .bold { font-weight: 700; }
        .upper { text-transform: uppercase; }
        .small { font-size: 10pt; }
        .divider {
            margin: 16px 0 18px;
            letter-spacing: 1px;
            white-space: nowrap;
            overflow: hidden;
        }
        .section-title {
            font-weight: 700;
            margin-top: 18px;
            margin-bottom: 8px;
        }
        .meta {
            width: 100%;
            margin-top: 12px;
            margin-bottom: 14px;
        }
        .meta td {
            padding: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10pt;
        }
        th, td {
            border: 1px solid #1f2937;
            padding: 6px 8px;
            vertical-align: top;
        }
        th {
            background: #f3f4f6;
        }
        .no-border td, .no-border th {
            border: none;
            padding: 2px 0;
        }
        .signature {
            margin-top: 34px;
            width: 100%;
        }
        .signature .block {
            width: 260px;
            margin-left: auto;
            text-align: center;
        }
        .spacer {
            height: 10px;
        }
    </style>
</head>
<body>
@php
    $scopeLabel = $scopeLabel ?? 'Semua TPU';
    $periodLabel = $periodLabel ?? 'Bulanan';
    $monthLabel = $monthLabel ?? now()->translatedFormat('F');
    $yearLabel = $yearLabel ?? now()->format('Y');
    $reportDate = $reportDate ?? now()->translatedFormat('d F Y');
    $ringkasanRows = collect($ringkasanRows ?? []);
    $rekapPelayananRows = collect($rekapPelayananRows ?? []);
    $dataPemakamanRows = collect($dataPemakamanRows ?? []);
    $statistikRows = collect($statistikRows ?? []);
    $nomor = $nomor ?? '....................................';
    $lampiran = $lampiran ?? '....................................';
    $perihal = $perihal ?? ('Laporan Bulanan Pelayanan dan Pendataan Tempat Pemakaman Umum Bulan ' . $monthLabel . ' Tahun ' . $yearLabel);
    $tujuanKepalaDinas = $tujuanKepalaDinas ?? 'Kepala Dinas Lingkungan Hidup';
    $tujuanWilayah = $tujuanWilayah ?? 'Kota/Kabupaten ................................';
@endphp

    <div class="center bold upper">PEMERINTAH KOTA/KABUPATEN .................................</div>
    <div class="center bold upper">DINAS LINGKUNGAN HIDUP</div>
    <div class="center bold upper">UNIT PELAKSANA TEKNIS DAERAH (UPTD) TEMPAT PEMAKAMAN UMUM</div>
    <div class="center">Alamat: Jl. ............................................................<br>Telepon: ..................... Email: ................................</div>

    <div class="divider center">=================================================================</div>

    <div class="center bold upper" style="font-size: 14pt;">
        LAPORAN BULANAN PELAYANAN DAN PENDATAAN TEMPAT PEMAKAMAN UMUM
    </div>
    <div class="center bold upper">UPTD TEMPAT PEMAKAMAN UMUM (TPU) {{ $scopeLabel }}</div>

    <table class="no-border meta">
        <tr>
            <td style="width: 90px;">Nomor</td>
            <td style="width: 20px;">:</td>
            <td>{{ $nomor }}</td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td>:</td>
            <td>{{ $lampiran }}</td>
        </tr>
        <tr>
            <td>Perihal</td>
            <td>:</td>
            <td>{{ $perihal }}</td>
        </tr>
    </table>

    <p>Kepada Yth.</p>
    <p class="bold">{{ $tujuanKepalaDinas }}</p>
    <p>{{ $tujuanWilayah }}</p>
    <p>di Tempat</p>

    <p>Dengan hormat,</p>

    <p>
        Dalam rangka pelaksanaan tugas dan fungsi pengelolaan Tempat Pemakaman Umum (TPU), bersama ini kami sampaikan laporan kegiatan pelayanan pemakaman pada UPTD TPU {{ $scopeLabel }} selama periode Bulan {{ $monthLabel }} Tahun {{ $yearLabel }}.
    </p>

    <p>Adapun rekapitulasi data pelayanan pemakaman adalah sebagai berikut:</p>

    <div class="section-title">A. RINGKASAN DATA PEMAKAMAN</div>
    <table>
        <tbody>
            @forelse($ringkasanRows as $item)
                <tr>
                    <td style="width: 55%;">{{ $loop->iteration }}. {{ $item['label'] ?? '-' }}</td>
                    <td style="width: 10%; text-align:center;">:</td>
                    <td>{{ $item['value'] ?? '0' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">B. REKAPITULASI PELAYANAN PEMAKAMAN</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>TPU</th>
                <th>Pemakaman Baru</th>
                <th>Perpanjangan</th>
                <th>Menunggu</th>
                <th>Disetujui</th>
                <th>Ditolak</th>
                <th>Total Permohonan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekapPelayananRows as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item['tpu'] ?? '-' }}</td>
                    <td>{{ $item['pemakaman_baru'] ?? 0 }}</td>
                    <td>{{ $item['perpanjangan'] ?? 0 }}</td>
                    <td>{{ $item['menunggu'] ?? 0 }}</td>
                    <td>{{ $item['disetujui'] ?? 0 }}</td>
                    <td>{{ $item['ditolak'] ?? 0 }}</td>
                    <td>{{ $item['total'] ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">C. DATA PEMAKAMAN BERDASARKAN TPU</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>TPU</th>
                <th>Total Jenazah</th>
                <th>Total Makam</th>
                <th>Laki-laki</th>
                <th>Perempuan</th>
                <th>Total Permohonan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataPemakamanRows as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item['tpu'] ?? '-' }}</td>
                    <td>{{ $item['total_jenazah'] ?? 0 }}</td>
                    <td>{{ $item['total_makam'] ?? 0 }}</td>
                    <td>{{ $item['laki_laki'] ?? 0 }}</td>
                    <td>{{ $item['perempuan'] ?? 0 }}</td>
                    <td>{{ $item['permohonan'] ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">D. STATISTIK PEMAKAMAN</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Blok / Zona</th>
                <th style="width: 120px;">Jumlah Makam</th>
            </tr>
        </thead>
        <tbody>
            @forelse($statistikRows as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item['blok_zona'] ?? '-' }}</td>
                    <td>{{ $item['jumlah'] ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">E. KENDALA DAN TINDAK LANJUT</div>
    <ol>
        <li>.....................................................................</li>
        <li>.....................................................................</li>
        <li>.....................................................................</li>
    </ol>

    <div class="section-title">F. KESIMPULAN</div>
    <p>
        Berdasarkan data yang telah dihimpun, pelayanan pemakaman pada UPTD TPU {{ $scopeLabel }} selama Bulan {{ $monthLabel }} Tahun {{ $yearLabel }} telah terlaksana dengan baik. Data ini mencakup ringkasan pelayanan, rekapitulasi permohonan, distribusi data per TPU, serta statistik pemakaman pada blok dan zona terkait.
    </p>

    <p>
        Demikian laporan ini disampaikan untuk menjadi bahan informasi dan evaluasi. Atas perhatian dan kerja sama yang baik, kami ucapkan terima kasih.
    </p>

    <div class="signature">
        <div class="block">
            <p>{{ $reportDate }}, ...................... 20....</p>
            <p class="bold">Kepala UPTD TPU</p>
            <div class="spacer"></div>
            <div class="spacer"></div>
            <div class="spacer"></div>
            <p>............................</p>
            <p class="small">Materai 10.000</p>
            <div class="spacer"></div>
            <p>(........................................)</p>
            <p>NIP. ....................................</p>
            <br>
            <p>Mengetahui,</p>
            <p class="bold">Kepala Dinas Perumahan Rakyat,<br>Kawasan Permukiman dan Pertanahan</p>
            <div class="spacer"></div>
            <div class="spacer"></div>
            <div class="spacer"></div>
            <p>(........................................)</p>
            <p>NIP. ....................................</p>
        </div>
    </div>
</body>
</html>
