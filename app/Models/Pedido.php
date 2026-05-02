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
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        return 'PED-' . date('Y') . '-' . str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Scope a query by status
     */
    public function scopeEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}
