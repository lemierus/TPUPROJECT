<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <table border="0" style="margin-bottom: 12px;">
        <tr>
            <td><strong>Laporan Pemakaman TPU {{ auth()->user()->tpu }}</strong></td>
        </tr>
        <tr>
            <td>
                {{ ($isKepalaReport ?? false) ? 'Gabungan data pemakaman, permohonan, dan jenazah.' : 'Gabungan data permohonan dan data jenazah.' }}
            </td>
        </tr>
        <tr>
            <td>Periode: {{ $filter ?? 'harian' }}</td>
        </tr>
    </table>

    @if($isKepalaReport ?? false)
        <table border="1" cellpadding="4" cellspacing="0" style="margin-bottom: 12px; width: 100%;">
            <tr>
                <th>Total Pemakaman</th>
                <th>Permohonan Masuk</th>
                <th>Laki-laki</th>
                <th>Perempuan</th>
                <th>Total Data</th>
                <th>Menunggu</th>
                <th>Disetujui</th>
                <th>Ditolak</th>
            </tr>
            <tr>
                <td>{{ $totalPemakaman ?? 0 }}</td>
                <td>{{ $totalPermohonan ?? 0 }}</td>
                <td>{{ $laki ?? 0 }}</td>
                <td>{{ $perempuan ?? 0 }}</td>
                <td>{{ $total ?? 0 }}</td>
                <td>{{ $permohonanMenunggu ?? 0 }}</td>
                <td>{{ $permohonanDisetujui ?? 0 }}</td>
                <td>{{ $permohonanDitolak ?? 0 }}</td>
            </tr>
        </table>
    @else
        <table border="1" cellpadding="4" cellspacing="0" style="margin-bottom: 12px; width: 100%;">
            <tr>
                <th>Total Data</th>
                <th>Permohonan Masuk</th>
                <th>Data Jenazah</th>
                <th>Makam Terhubung</th>
            </tr>
            <tr>
                <td>{{ $total ?? 0 }}</td>
                <td>{{ $totalPermohonan ?? 0 }}</td>
                <td>{{ $totalJenazah ?? 0 }}</td>
                <td>{{ $totalMakamTerhubung ?? 0 }}</td>
            </tr>
        </table>
    @endif

    <table border="1">
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
                    <td>{{ $row['source_label'] }}</td>
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
                    <td colspan="14">Tidak ada data laporan</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
