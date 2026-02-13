<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $url)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
        ->subject('Recuperação de Senha')
        ->greeting('Olá!')
        ->line('Você solicitou a alteração de sua senha.')
        ->action('Alterar Senha', $this->url)
        ->line('Este link expirará em 15 minutos.')
        ->line('Se você não solicitou isso, ignore este e-mail.');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
