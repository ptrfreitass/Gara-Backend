<?php
// app/Notifications/VerifyEmailCodeNotification.php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailCodeNotification extends Notification
{
    public function __construct(private readonly string $code) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Seu código de verificação')
            ->greeting("Olá, {$notifiable->name}!")
            ->line('Use o código abaixo para verificar sua conta:')
            ->line("## {$this->code}")
            ->line('Este código expira em **15 minutos**.')
            ->line('Se não criou uma conta, ignore este e-mail.');
    }
}