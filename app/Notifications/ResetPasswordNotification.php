<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;
    
    /**
     * Create a new notification instance.
     */
    public function __construct(public string $url)
    {
        // $this->url = $url;
    }

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
        return (new MailMessage)
        ->subject('Recuperação de Senha')
        ->greeting('Olá!')
        ->line('Você solicitou a alteração de sua senha.')
        ->action('Alterar Senha', $this->url) // A URL assinada vai aqui
        ->line('Este link expirará em 15 minutos.')
        ->line('Se você não solicitou isso, ignore este e-mail.');
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
