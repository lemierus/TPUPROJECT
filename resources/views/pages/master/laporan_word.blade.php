<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pemakaman {{ $scopeLabel ?? 'TPU' }}</title>
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
        .meta { margin-top: 14px; margin-bottom: 14px; }
        .section-title {
            font-weight: 700;
            margin-top: 18px;
            margin-bottom: 8px;
        }
        .divider {
            margin: 18px 0;
            letter-spacing: 1px;
            white-space: nowrap;
            overflow: hidden;
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
    $reportRows = collect($reportRows ?? []);
    $dataJenazahRows = collect($dataJenazahRows ?? $reportRows->where('source', 'permohonan')->values());
    $blokZonaStats = collect($blokZonaStats ?? []);
    $nomor = $nomor ?? '....................................';
    $lampiran = $lampiran ?? '....................................';
    $perihal = $perihal ?? ('Laporan Data Pemakaman ' . $periodLabel . ' Tahun ' . $yearLabel);
    $tujuanKepalaDinas = $tujuanKepalaDinas ?? 'Kepala Dinas Perumahan Rakyat, Kawasan Permukiman dan Pertanahan';
    $tujuanWilayah = $tujuanWilayah ?? 'Kota/Kabupaten ................................';
    $tanggalCetak = $tanggalCetak ?? now()->translatedFormat('d F Y');
@endphp

    <div class="center bold upper">
        PEMERINTAH KOTA/KABUPATEN .................................
    </div>
    <div class="center bold upper">
        DINAS PERUMAHAN RAKYAT, KAWASAN PERMUKIMAN DAN PERTANAHAN
    </div>
    <div class="center bold upper">
        UNIT PELAKSANA TEKNIS DAERAH (UPTD) TEMPAT PEMAKAMAN UMUM
    </div>
    <div class="center">
        Alamat: Jl. ............................................................<br>
        Telepon: ..................... Email: ................................
    </div>

    <div class="divider center">
        =================================================================
    </div>

    <div class="center bold upper" style="font-size: 14pt;">
        LAPORAN DATA PEMAKAMAN {{ strtoupper($periodLabel) }}
    </div>
    <div class="center bold upper">
        UPTD TEMPAT PEMAKAMAN UMUM (TPU) {{ $scopeLabel }}
    </div>

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
        Dalam rangka pelaksanaan tugas dan fungsi pengelolaan Tempat Pemakaman Umum (TPU),
        bersama ini kami sampaikan laporan kegiatan pelayanan pemakaman pada UPTD TPU {{ $scopeLabel }}
        selama periode {{ $periodLabel }} {{ $yearLabel }}.
    </p>

    <p>Adapun rekapitulasi data pelayanan pemakaman adalah sebagai berikut:</p>

    <div class="section-title">A. DATA PEMAKAMAN</div>
    <table>
        <tbody>
            <tr>
                <td style="width: 55%;">1. Jumlah pemakaman baru</td>
                <td style="width: 10%; text-align:center;">:</td>
                <td>{{ $totalPemakamanBaru ?? 0 }} makam</td>
            </tr>
            <tr>
                <td>2. Jumlah perpanjangan makam</td>
                <td style="text-align:center;">:</td>
                <td>{{ $totalPerpanjangan ?? 0 }} makam</td>
            </tr>
            <tr>
                <td>3. Jumlah perawatan makam</td>
                <td style="text-align:center;">:</td>
                <td>{{ $totalPerawatan ?? 0 }} makam</td>
            </tr>
            <tr>
                <td>4. Jumlah makam aktif</td>
                <td style="text-align:center;">:</td>
                <td>{{ $totalMakamAktif ?? 0 }} makam</td>
            </tr>
            <tr>
                <td>5. Jumlah makam yang berakhir masa sewa</td>
                <td style="text-align:center;">:</td>
                <td>{{ $totalMakamBerakhirSewa ?? 0 }} makam</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">B. DATA JENAZAH YANG DIMAKAMKAN</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Nama Jenazah</th>
                <th>NIK</th>
                <th>Tanggal Pemakaman</th>
                <th>Blok/Zona</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataJenazahRows as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row['nama'] ?? '-' }}</td>
                    <td>{{ $row['nik'] ?? '-' }}</td>
                    <td>{{ $row['tanggal_input_label'] ?? '-' }}</td>
                    <td>{{ trim(($row['blok'] ?? '-') . ' / ' . ($row['zona'] ?? '-')) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">C. STATISTIK PEMAKAMAN BERDASARKAN BLOK/ZONA</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Blok/Zona</th>
                <th style="width: 120px;">Jumlah Makam</th>
            </tr>
        </thead>
        <tbody>
            @forelse($blokZonaStats as $item)
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

    <div class="section-title">D. KENDALA DAN TINDAK LANJUT</div>
    <ol>
        <li>.....................................................................</li>
        <li>.....................................................................</li>
        <li>.....................................................................</li>
    </ol>

    <div class="section-title">E. KESIMPULAN</div>
    <p>
        Berdasarkan data yang telah dihimpun, pelayanan pemakaman pada UPTD TPU {{ $scopeLabel }}
        selama {{ $periodLabel }} {{ $yearLabel }} telah terlaksana dengan baik. Jumlah pelayanan yang
        diberikan mencakup pemakaman baru, perpanjangan makam, serta perawatan makam sesuai dengan prosedur
        yang berlaku. Data ini diharapkan dapat menjadi bahan evaluasi dan pengambilan keputusan dalam
        pengelolaan serta pengembangan pelayanan pemakaman di masa yang akan datang.
    </p>

    <p>
        Demikian laporan ini disampaikan untuk menjadi bahan informasi dan evaluasi. Atas perhatian dan
        kerja sama yang baik, kami ucapkan terima kasih.
    </p>

    <div class="signature">
        <div class="block">
            <p>{{ $tanggalCetak }}, ...................... 20....</p>
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
