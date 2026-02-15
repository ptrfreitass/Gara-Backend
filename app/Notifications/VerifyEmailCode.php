<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VerifyEmailCode extends Notification implements ShouldQueue
{
    use Queueable;


    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $verificationCode
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        Log::info('📧 [NOTIFICATION] Preparando email de verificação', [
            'user_id' => $notifiable->id,
            'email' => $notifiable->email,
            'code' => $this->verificationCode
        ]);

        return (new MailMessage)
            ->subject('Gara - Seu codigo de verificacao')
            ->greeting('Olá! Agradecemos por se registrar, agora falta pouco para organizar sua vida!')
            ->line('Seu código de verificação é para acesso ao sistema Gara é:')
            ->line('**' . $this->verificationCode . '**')
            ->line('Este código expirará em 15 minutos.')
            ->line('Se você não solicitou este código, ignore este e-mail.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
