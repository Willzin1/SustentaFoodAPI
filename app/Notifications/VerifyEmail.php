<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmail extends Notification
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
        $verificationUrl = url('/email/verify/' . $notifiable->getKey());

        return (new MailMessage)
        ->subject('Confirme seu e-mail')
        ->greeting('Olá, ' . $notifiable->name . '!')
        ->line('Clique no botão abaixo para confirmar seu e-mail e ativar sua conta.')
        ->action('Confirmar E-mail', $verificationUrl)
        ->line('Se você não criou uma conta, ignore este email.')
        ->salutation('Atenciosamente, Sustenta Food');
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
