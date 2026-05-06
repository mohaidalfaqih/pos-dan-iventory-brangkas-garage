<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code,
        public string $type,
        public ?string $newEmail = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->type) {
            'password_change' => 'Kode Verifikasi Ganti Password',
            'forgot_password' => 'Kode Verifikasi Reset Password',
            default           => 'Kode Verifikasi Ubah Email',
        };

        $description = match ($this->type) {
            'password_change' => 'Anda meminta untuk mengganti password akun Brangkas Garage Anda.',
            'forgot_password' => 'Anda meminta untuk mereset password akun Brangkas Garage Anda.',
            default           => 'Anda meminta untuk mengubah email akun Brangkas Garage Anda ke <strong>' . e($this->newEmail) . '</strong>.',
        };

        return (new MailMessage)
            ->subject($subject . ' - Brangkas Garage')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line($description)
            ->line('Gunakan kode verifikasi berikut:')
            ->line('**' . $this->code . '**')
            ->line('Kode ini berlaku selama **10 menit**.')
            ->line('Jika Anda tidak melakukan permintaan ini, abaikan email ini dan segera hubungi admin.')
            ->salutation('Salam, Tim Brangkas Garage');
    }
}
