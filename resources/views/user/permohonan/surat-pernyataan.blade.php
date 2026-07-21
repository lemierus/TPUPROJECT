<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Pernyataan Kepemilikan Makam</title>
    <style>
        /* dompdf hanya mendukung sebagian CSS, jadi layout dibuat dengan tabel & elemen sederhana */

        @page {
            size: A4;
            margin: 20mm 20mm 18mm 20mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            color: #000;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        .kop {
            width: 100%;
            border-bottom: 3px solid #000;
            padding-bottom: 8px;
            margin-bottom: 18px;
        }

        .kop table {
            width: 100%;
            border-collapse: collapse;
        }

        .kop table td {
            vertical-align: middle;
            padding: 0;
        }

        .kop {
            width: 100%;
            border-bottom: 3px solid #000;
            padding-bottom: 8px;
            margin-bottom: 18px;
        }

        .kop-instansi {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            text-align: left;
        }

        .kop-sub {
            font-size: 11pt;
            margin: 0;
            text-align: left;
        }

        .judul {
            text-align: center;
            margin: 22px 0 4px 0;
        }

        .judul h1 {
            font-size: 13pt;
            text-decoration: underline;
            text-transform: uppercase;
            margin: 0;
        }

        .judul .nomor {
            font-size: 11pt;
            margin-top: 2px;
            text-align: center;
        }

        .isi {
            margin-top: 22px;
            text-align: justify;
        }

        table.data-diri,
        table.data-object {
            width: 100%;
            margin: 12px 0 12px 22px;
            border-collapse: collapse;
        }

        table.data-diri td,
        table.data-object td {
            padding: 2px 4px;
            vertical-align: top;
            text-align: left;
        }

        table.data-diri td.label,
        table.data-object td.label {
            width: 32%;
        }

        table.data-diri td.titik,
        table.data-object td.titik {
            width: 3%;
        }

        .ttd-wrapper {
            width: 100%;
            margin-top: 48px;
        }

        .ttd-wrapper table {
            width: 100%;
            border-collapse: collapse;
        }

        .ttd-kolom {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .ttd-spasi {
            height: 70px;
        }

        .footer-note {
            margin-top: 40px;
            font-size: 9.5pt;
            color: #444;
            text-align: justify;
        }
    </style>
</head>
<body>

<!-- <div class="kop">
    {{-- Jika sudah ada logo resmi, tambahkan <img> di sini dan lihat catatan di bawah --}}
    <p class="kop-instansi">Pemerintah Kota Padang</p>
    <p class="kop-instansi">Dinas Lingkungan Hidup</p>
    <p class="kop-sub">Unit Pelaksana Teknis Tempat Pemakaman Umum (UPT TPU)</p>
    <p class="kop-sub">Jalan By Pass KM 12, Kota Padang, Sumatera Barat</p>
</div> -->

    <div class="judul">
        <h1>Surat Pernyataan Kepemilikan Makam</h1>
        <div class="nomor">Nomor: {{ str_pad($permohonan->id, 4, '0', STR_PAD_LEFT) }}/SP-TPU/{{ optional($permohonan->updated_at)->format('m') ?? now()->format('m') }}/{{ optional($permohonan->updated_at)->format('Y') ?? now()->format('Y') }}</div>
    </div>

    <div class="isi">
        <p>Yang bertanda tangan di bawah ini, pihak UPT Tempat Pemakaman Umum (TPU) {{ $permohonan->tpu }} pada
        Dinas Lingkungan Hidup Kota Padang, dengan ini menyatakan bahwa:</p>

        <table class="data-diri">
            <tr>
                <td class="label">Nama Ahli Waris</td>
                <td class="titik">:</td>
                <td>{{ $permohonan->nama_ahli_waris ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Nama Pemohon</td>
                <td class="titik">:</td>
                <td>{{ $permohonan->nama_pemohon ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Hubungan Keluarga</td>
                <td class="titik">:</td>
                <td>{{ $permohonan->hubungan_keluarga ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Nomor Telepon/WhatsApp</td>
                <td class="titik">:</td>
                <td>{{ $permohonan->no_hp_ahli_waris ?? '-' }}</td>
            </tr>
        </table>

        <p>adalah benar merupakan ahli waris yang sah dan tercatat pada sistem TAMPU sebagai pihak yang berhak
        atas makam dengan data jenazah dan lokasi sebagai berikut:</p>

        <table class="data-object">
            <tr>
                <td class="label">Nama Jenazah</td>
                <td class="titik">:</td>
                <td>{{ $jenazah->nama ?? $permohonan->nama_jenazah ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">NIK</td>
                <td class="titik">:</td>
                <td>{{ $jenazah->nik ?? $permohonan->nik_jenazah ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Wafat</td>
                <td class="titik">:</td>
                <td>
                    {{ $jenazah?->tanggal_wafat?->format('d-m-Y') ?? ($permohonan->tanggal_wafat ? \Illuminate\Support\Carbon::parse($permohonan->tanggal_wafat)->format('d-m-Y') : '-') }}
                </td>
            </tr>
            <tr>
                <td class="label">Kode Makam</td>
                <td class="titik">:</td>
                <td>{{ $jenazah->kode_makam ?? $makam?->kode_makam ?? $permohonan->kode_makam ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Blok / Zona / Nomor</td>
                <td class="titik">:</td>
                <td>
                    {{ $jenazah->blok ?? $makam?->blok ?? $permohonan->blok ?? '-' }} /
                    {{ $jenazah->zona ?? $makam?->zona ?? $permohonan->zona ?? '-' }} /
                    {{ $jenazah->nomor_makam ?? $makam?->nomor ?? $permohonan->nomor_makam ?? $permohonan->no_makam ?? '-' }}
                </td>
            </tr>
            <tr>
                <td class="label">Lokasi TPU</td>
                <td class="titik">:</td>
                <td>{{ $jenazah->tpu ?? $makam?->tpu ?? $permohonan->tpu ?? '-' }}</td>
            </tr>
        </table>

        <p>Surat pernyataan ini diterbitkan berdasarkan permohonan Nomor {{ $permohonan->id }} yang telah disetujui
        oleh petugas berwenang, dan dapat digunakan sebagai bukti keterangan kepemilikan makam sebagaimana
        dimaksud selama masa sewa makam masih berlaku sesuai ketentuan yang berlaku pada Dinas Lingkungan Hidup
        Kota Padang.</p>

        <p>Demikian surat pernyataan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
    </div>

    <div class="ttd-wrapper">
        <table>
            <tr>
                <td class="ttd-kolom">&nbsp;</td>
                <td class="ttd-kolom">
                    Padang, {{ now()->translatedFormat('d F Y') }}<br>
                    Kepala UPT TPU Kota Padang<br>
                    <div class="ttd-spasi"></div>
                    <strong>(.......................................)</strong><br>
                    NIP.
                </td>
            </tr>
        </table>
    </div>

    <div class="footer-note">
        Dokumen ini digenerate otomatis melalui sistem TAMPU dan sah tanpa memerlukan cap basah apabila
        digunakan untuk keperluan administrasi internal. Untuk keperluan resmi di luar instansi, silakan
        legalisasi surat ini ke Kantor UPT TPU terkait.
    </div>

</body>
</html>