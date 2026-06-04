<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
</head>
<body>
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
