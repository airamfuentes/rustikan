<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoCancelado extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Pedido $pedido,
        public readonly string $tipoReembolso = 'ninguno',
        public readonly ?string $motivo = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pedido ' . $this->pedido->numero_pedido . ' cancelado — Rustikan',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pedido-cancelado',
            with: [
                'pedido'         => $this->pedido,
                'tipoReembolso'  => $this->tipoReembolso,
                'motivo'         => $this->motivo,
            ],
        );
    }
}
