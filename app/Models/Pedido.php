<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'numero_pedido',
        'estado',
        'subtotal',
        'gastos_envio',
        'total',
        'direccion_envio',
        'telefono_contacto',
        'notas',
        'fecha_entrega',
        'metodo_pago',
        'stripe_payment_intent_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'gastos_envio' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha_entrega' => 'datetime',
    ];

    /**
     * Get the user that owns the pedido
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in this pedido
     */
    public function items()
    {
        return $this->hasMany(PedidoItem::class);
    }

    /**
     * Reseña asociada a este pedido (1:1 — un pedido como mucho una reseña).
     */
    public function resena()
    {
        return $this->hasOne(Resena::class);
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        do {
            $number = 'PED-' . date('Y') . '-' . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::where('numero_pedido', $number)->exists());

        return $number;
    }

    /**
     * Scope a query by status
     */
    public function scopeEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}
