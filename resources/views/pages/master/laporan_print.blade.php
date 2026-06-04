<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pemakaman TPU {{ auth()->user()->tpu }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111827;
            margin: 24px;
        }
        h1, h2, h3, p {
            margin: 0 0 8px;
        }
        .muted {
            color: #6b7280;
            font-size: 12px;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin: 18px 0 24px;
        }
        .card {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 12px;
        }
        .card .label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .card .value {
            font-size: 24px;
            font-weight: 700;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 8px;
            vertical-align: top;
            text-align: left;
        }
        th {
            background: #f3f4f6;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
        }
        .badge-primary { background: #1E3E62; }
        .badge-success { background: #047857; }
        .badge-secondary { background: #6b7280; }
        @media print {
            body { margin: 12mm; }
        }
    </style>
</head>
<body onload="window.print()">
    <h2>Laporan Pemakaman TPU {{ auth()->user()->tpu }}</h2>
    <p class="muted">Gabungan data permohonan dan data jenazah. Periode: {{ $filter ?? 'harian' }}</p>

    <div class="summary">
        <div class="card">
            <div class="label">Total Data</div>
            <div class="value">{{ $total ?? 0 }}</div>
        </div>
        <div class="card">
            <div class="label">Permohonan Masuk</div>
            <div class="value">{{ $totalPermohonan ?? 0 }}</div>
        </div>
        <div class="card">
            <div class="label">Data Jenazah</div>
            <div class="value">{{ $totalJenazah ?? 0 }}</div>
        </div>
        <div class="card">
            <div class="label">Makam Terhubung</div>
            <div class="value">{{ $totalMakamTerhubung ?? 0 }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Sumber</th>
                <th>Nama</th>
                <th>NIK</th>
                <th>Kelamin</th>
                <th>Input</th>
                <th>Wafat</th>
                <th>Kode Makam</th>
                <th>Blok</th>
                <th>Zona</th>
                <th>Nomor</th>
                <th>Status Permohonan</th>
                <th>Status</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportRows as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <span class="badge {{ $row['source'] === 'permohonan' ? 'badge-primary' : 'badge-success' }}">
                            {{ $row['source_label'] }}
                        </span>
                    </td>
                    <td>{{ $row['nama'] }}</td>
                    <td>{{ $row['nik'] }}</td>
                    <td>{{ $row['jenis_kelamin'] }}</td>
                    <td>{{ $row['tanggal_input_label'] }}</td>
                    <td>{{ $row['tanggal_wafat_label'] }}</td>
                    <td>{{ $row['kode_makam'] }}</td>
                    <td>{{ $row['blok'] }}</td>
                    <td>{{ $row['zona'] }}</td>
                    <td>{{ $row['nomor_makam'] }}</td>
                    <td>{{ $row['source'] === 'permohonan' ? ucfirst($row['status_permohonan'] ?? '-') : '-' }}</td>
                    <td>{{ ucfirst($row['status_makam'] ?? 'Belum Ada') }}</td>
                    <td>{{ $row['source'] === 'permohonan' ? $row['nama_ahli_waris'].' | '.$row['catatan'] : $row['catatan'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" style="text-align:center;">Tidak ada data laporan</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
