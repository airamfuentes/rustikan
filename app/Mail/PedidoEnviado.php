<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoEnviado extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Pedido $pedido
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu pedido ' . $this->pedido->numero_pedido . ' está en camino! — Rustikan',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pedido-enviado',
        );
    }
}
