<?php

namespace App\Mail;

use App\Models\Producto;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StockDisponible extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Producto $producto,
        public readonly string   $destinatarioNombre,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡' . $this->producto->nombre . ' vuelve a estar disponible! — Rustikan',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.stock-disponible',
        );
    }
}
