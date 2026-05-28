<?php

use App\Models\Jenazah;
use App\Models\Makam;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('creates a linked jenazah record without auto-creating a makam for a new permohonan', function () {
    $petugas = User::create([
        'name' => 'Petugas TPU',
        'email' => 'petugas@example.com',
        'password' => bcrypt('password'),
        'role' => 'petugas',
        'tpu' => 'TPU Tunggul Hitam',
    ]);

    $permohonan = Permohonan::create([
        'user_id' => $petugas->id,
        'tpu' => 'TPU Tunggul Hitam',
        'jenis_permohonan' => 'makam_baru',
        'nama_pemohon' => 'Pemohon',
        'nama_jenazah' => 'Ahmad',
        'nik_jenazah' => '1234567890123456',
        'tempat_lahir' => 'Padang',
        'tanggal_lahir' => '1990-01-01',
        'tanggal_wafat' => '2026-01-10',
        'jenis_kelamin' => 'Laki-laki',
        'agama' => 'Islam',
        'alamat' => 'Alamat test',
        'nama_ahli_waris' => 'Waris',
        'no_hp_ahli_waris' => '08123456789',
        'hubungan_keluarga' => 'Anak',
        'scan_ktp_ahli_waris' => 'ktp.jpg',
        'scan_kk' => 'kk.jpg',
        'surat_kematian' => 'surat.jpg',
        'kode_makam' => 'MK-001',
        'blok' => 'A',
        'zona' => 'I',
        'nomor_makam' => '10',
        'keterangan' => 'Makam baru',
        'status' => 'menunggu',
    ]);

    $permohonan->persistJenazahRecord();
    $permohonan->refresh();

    expect($permohonan->jenazah_id)->not->toBeNull();

    $jenazah = Jenazah::find($permohonan->jenazah_id);
    expect($jenazah)->not->toBeNull();
    expect($jenazah->nama)->toBe('Ahmad');
    expect($jenazah->nik)->toBe('1234567890123456');
    expect($jenazah->kode_makam)->toBeNull();
    expect($jenazah->blok)->toBeNull();
    expect($jenazah->zona)->toBeNull();
    expect($jenazah->nomor_makam)->toBeNull();
    expect($jenazah->keterangan)->toBe('Makam baru');

    expect(Makam::where('kode_makam', 'MK-001')->exists())->toBeFalse();
});

it('creates linked jenazah data on submission and shows it in the TPU data jenazah view', function () {
    Storage::fake('public');

    $pemohon = User::create([
        'name' => 'Pemohon Uji',
        'email' => 'pemohon@example.com',
        'password' => bcrypt('password'),
        'role' => 'user',
        'tpu' => 'TPU Tunggul Hitam',
    ]);

    $petugas = User::create([
        'name' => 'Petugas TPU',
        'email' => 'petugas-data@example.com',
        'password' => bcrypt('password'),
        'role' => 'petugas',
        'tpu' => 'TPU Tunggul Hitam',
    ]);

    $response = $this->actingAs($pemohon)->post(route('user.permohonan.store'), [
        'tpu' => 'TPU Tunggul Hitam',
        'jenis_permohonan' => 'makam_baru',
        'nama_jenazah' => 'Ahmad',
        'nik_jenazah' => '1234567890123456',
        'tempat_lahir' => 'Padang',
        'tanggal_lahir' => '1990-01-01',
        'tanggal_wafat' => '2026-01-10',
        'jenis_kelamin' => 'Laki-laki',
        'agama' => 'Islam',
        'nama_ahli_waris' => 'Waris',
        'no_hp_ahli_waris' => '08123456789',
        'hubungan_keluarga' => 'Anak',
        'alamat' => 'Alamat test',
        'kode_makam' => 'MK-001',
        'blok' => 'A',
        'zona' => 'I',
        'nomor_makam' => '10',
        'keterangan' => 'Makam baru',
        'scan_ktp_ahli_waris' => UploadedFile::fake()->image('ktp.jpg'),
        'scan_kk' => UploadedFile::fake()->image('kk.jpg'),
        'surat_kematian' => UploadedFile::fake()->image('surat.jpg'),
    ]);

    $response->assertRedirect(route('user.dashboard'));

    $permohonan = Permohonan::where('user_id', $pemohon->id)->latest('id')->first();
    expect($permohonan)->not->toBeNull();
    expect($permohonan->status)->toBe('menunggu');
    expect($permohonan->jenazah_id)->not->toBeNull();

    $jenazah = Jenazah::find($permohonan->jenazah_id);
    expect($jenazah)->not->toBeNull();
    expect($jenazah->tpu)->toBe('TPU Tunggul Hitam');
    expect($jenazah->nama)->toBe('Ahmad');
    expect($jenazah->nik)->toBe('1234567890123456');

    $viewResponse = $this->actingAs($petugas)->get(route('petugas.data-jenazah'));
    $viewResponse->assertOk();
    $viewResponse->assertSee('Ahmad');
    $viewResponse->assertSee('Data jenazah dari permohonan TPU ini');
});
