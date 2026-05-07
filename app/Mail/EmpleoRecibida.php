<?php

namespace App\Mail;

use App\Models\SolicitudEmpleo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmpleoRecibida extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly SolicitudEmpleo $solicitud,
        public readonly string $puestoLabel,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hemos recibido tu candidatura — Rustikan',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.empleo-recibida',
        );
    }
}
