<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VerificacionEmail extends Notification
{
    public function __construct(
        public readonly string $codigo
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Tu código de verificación — Rustikan')
            ->view('emails.verificacion', [
                'codigo'         => $this->codigo,
                'nombreUsuario'  => $notifiable->name,
            ]);
    }
}
