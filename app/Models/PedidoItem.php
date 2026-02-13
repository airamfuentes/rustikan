<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PedidoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'tienda_id',
        'producto_nombre',
        'tienda_nombre',
        'producto_imagen',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Get the pedido that owns the item
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    /**
     * Get the producto
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * Get the tienda
     */
    public function tienda()
    {
        return $this->belongsTo(Tienda::class);
    }

    /**
     * Get producto name (from snapshot or relation)
     */
    public function getProductoNombreAttribute()
    {
        return $this->attributes['producto_nombre'] ?? $this->producto?->nombre ?? 'Producto eliminado';
    }

    /**
     * Get tienda name (from snapshot or relation)
     */
    public function getTiendaNombreAttribute()
    {
        return $this->attributes['tienda_nombre'] ?? $this->tienda?->nombre ?? 'Tienda eliminada';
    }
}
