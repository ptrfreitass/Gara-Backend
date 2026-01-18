<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailCode extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
        $code = cache()->get('verification_code)' . $notifiable->id);
        if(!$code) {
            $code = rand(100000, 999999);
            cache()->put('verification_code_' . $notifiable->id, $code, now()->addMinutes(15));
        }

        return (new MailMessage)
            ->subject('Seu código de verificação.')
            ->line('Seu código de verificação é:')
            ->line($code)
            ->line('O código expira em 15 minutos.');
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
