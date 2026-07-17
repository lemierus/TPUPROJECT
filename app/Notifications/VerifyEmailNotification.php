<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi Email Akun TAMPU')
            ->greeting('Halo ' . ($notifiable->name ?? 'Pengguna TAMPU') . ',')
            ->line('Terima kasih telah mendaftar di TAMPU (Sistem Informasi Terpadu Manajemen TPU Kota Padang).')
            ->line('Sebelum menggunakan layanan, silakan verifikasi alamat email Anda terlebih dahulu.')
            ->action('Verifikasi Email', $verificationUrl)
            ->line('Jika Anda tidak merasa membuat akun ini, abaikan email ini.')
            ->salutation('Salam, Tim TAMPU');
    }
}
