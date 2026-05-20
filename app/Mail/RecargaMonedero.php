<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecargaMonedero extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User  $user,
        public readonly float $cantidad,
        public readonly float $saldoNuevo,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: number_format($this->cantidad, 2, ',', '.') . ' RustiCoins añadidos a tu monedero — Rustikan',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recarga-monedero',
        );
    }
}
