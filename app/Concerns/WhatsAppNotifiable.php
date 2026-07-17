<?php

namespace App\Concerns;

use App\Models\Permohonan;
use App\Models\Tpu;

trait WhatsAppNotifiable
{
    /**
     * Build a WhatsApp URL for sending a notification to the ahli waris.
     */
    public function buildWhatsAppMessage(string $phoneNumber, string $message): ?string
    {
        if (empty($phoneNumber)) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        if (substr($cleaned, 0, 1) === '0') {
            $cleaned = '62' . substr($cleaned, 1);
        } elseif (substr($cleaned, 0, 2) !== '62') {
            $cleaned = '62' . $cleaned;
        }

        return 'https://wa.me/' . $cleaned . '?text=' . urlencode($message);
    }

    /**
     * Send status update notification via WhatsApp link for a permohonan.
     */
    public function notifyPermohonanStatus(Permohonan $permohonan): ?string
    {
        $phoneNumber = $permohonan->no_hp_ahli_waris ?? $permohonan->user?->no_hp;
        if (empty($phoneNumber)) {
            return null;
        }

        $statusLabel = match ($permohonan->status) {
            'disetujui' => 'DISETUJUI',
            'ditolak' => 'DITOLAK',
            default => strtoupper($permohonan->status ?? 'MENUNGGU'),
        };

        $message = sprintf(
            "Assalamu'alaikum Wr. Wb.\n\n"
            . "Yth. Bpk/Ibu %s,\n\n"
            . "Kami informasikan bahwa status permohonan makam baru Anda di %s telah *%s*.\n\n"
            . "%s\n\n"
            . "Terima kasih.\n\n"
            . "— Sistem Informasi Tempat Pemakaman Umum Kota Padang (TAMPU)",
            $permohonan->nama_ahli_waris ?? $permohonan->user?->name ?? 'Ahli Waris',
            $permohonan->tpu ?? 'TPU terkait',
            $statusLabel,
            $permohonan->status === 'disetujui'
                ? 'Silakan menghubungi petugas TPU untuk informasi lebih lanjut mengenai jadwal dan tata cara pemakaman.'
                : 'Alasan: ' . ($permohonan->catatan ?? 'Tidak ada keterangan lebih lanjut.')
        );

        return $this->buildWhatsAppMessage($phoneNumber, $message);
    }

    /**
     * Send sewa renewal reminder via WhatsApp link for a permohonan.
     */
    public function notifySewaReminder(Permohonan $permohonan): ?string
    {
        $phoneNumber = $permohonan->no_hp_ahli_waris ?? $permohonan->user?->no_hp;
        if (empty($phoneNumber)) {
            return null;
        }

        $dueAt = $permohonan->renewalDueAt();
        $level = $permohonan->renewalAlertLevel();
        $tenggatLabel = $dueAt?->format('d-m-Y') ?? '—';

        $warningText = $level === 'expired'
            ? 'masa sewa makam telah melewati batas waktu'
            : 'masa sewa makam akan segera berakhir';

        $message = sprintf(
            "Assalamu'alaikum Wr. Wb.\n\n"
            . "Yth. Bpk/Ibu %s,\n\n"
            . "Kami informasikan bahwa %s untuk jenazah a/n %s di %s.\n\n"
            . "Batas sewa makam: *%s*\n\n"
            . "%s\n\n"
            . "Terima kasih.\n\n"
            . "— Sistem Informasi TPU Kota Padang (TAMPU)",
            $permohonan->nama_ahli_waris ?? $permohonan->user?->name ?? 'Ahli Waris',
            $warningText,
            $permohonan->nama_jenazah ?? $permohonan->jenazah?->nama ?? '-',
            $permohonan->tpu ?? 'TPU terkait',
            $tenggatLabel,
            $level === 'expired'
                ? 'Segera lakukan perpanjangan sewa makam dengan menghubungi petugas TPU.'
                : 'Mohon segera lakukan perpanjangan sewa makam sebelum batas waktu berakhir.'
        );

        return $this->buildWhatsAppMessage($phoneNumber, $message);
    }

    /**
     * Get WhatsApp contact URL for TPU petugas.
     */
    public function getWhatsAppPetugasUrl(Permohonan $permohonan): ?string
    {
        $tpu = Tpu::where('nama', $permohonan->tpu)->with('waPetugas')->first();
        $waPetugas = $tpu?->waPetugas;

        if (! $waPetugas || empty($waPetugas->no_hp)) {
            return null;
        }

        $message = sprintf(
            "Assalamu'alaikum Wr. Wb.\n\n"
            . "Yth. Petugas %s,\n\n"
            . "Saya ingin menanyakan informasi terkait pemakaman di %s.\n\n"
            . "Mohon informasinya.\n\n"
            . "Terima kasih.",
            $waPetugas->name,
            $permohonan->tpu ?? 'TPU terkait'
        );

        return $this->buildWhatsAppMessage($waPetugas->no_hp, $message);
    }

    public function notifyDaruratPermohonan(Permohonan $permohonan): ?string
    {
        $tpu = Tpu::where('nama', $permohonan->tpu)->with('waPetugas')->first();
        $waPetugas = $tpu?->waPetugas;

        if (! $waPetugas || empty($waPetugas->no_hp)) {
            return null;
        }

        $message = sprintf(
            "Assalamu'alaikum Wr. Wb.\n\n"
            . "Permohonan darurat baru telah diajukan.\n\n"
            . "Nama jenazah: %s\n"
            . "TPU tujuan: %s\n"
            . "Nama ahli waris: %s\n"
            . "No. HP ahli waris: %s\n"
            . "Catatan: %s\n\n"
            . "Mohon segera ditindaklanjuti.\n\n",
            $permohonan->nama_jenazah ?? '-',
            $permohonan->tpu ?? '-',
            $permohonan->nama_ahli_waris ?? '-',
            $permohonan->no_hp_ahli_waris ?? '-',
            $permohonan->catatan ?? '-'
        );

        return $this->buildWhatsAppMessage($waPetugas->no_hp, $message);
    }
}
