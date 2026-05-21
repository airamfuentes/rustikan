<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\Notificacion;
use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MarcarPedidoEntregado implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $pedidoId) {}

    public function handle(): void
    {
        $pedido = Pedido::find($this->pedidoId);

        if (!$pedido || $pedido->estado !== 'enviado') {
            return;
        }

        $pedido->update(['estado' => 'entregado']);

        ActivityLog::log(
            'auto_entregado',
            "Pedido #{$pedido->numero_pedido} marcado automáticamente como entregado",
            'confirmado',
            'green',
            $pedido
        );

        if ($pedido->user_id) {
            Notificacion::enviar(
                $pedido->user_id,
                'pedido_entregado',
                '¡Pedido entregado!',
                "Tu pedido #{$pedido->numero_pedido} ha sido entregado. ¡Gracias por comprar en Rustikan!",
                route('pedidos.index'),
                'cart',
                'green'
            );
        }
    }
}
