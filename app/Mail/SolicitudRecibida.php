<?php

namespace App\Mail;

use App\Models\SolicitudCreacionTienda;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudRecibida extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly SolicitudCreacionTienda $solicitud
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hemos recibido tu solicitud — Rustikan',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.solicitud-recibida',
        );
    }
}
