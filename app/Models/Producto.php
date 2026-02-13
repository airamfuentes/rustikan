<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'tienda_id',
        'categoria_id',
        'nombre',
        'slug',
        'descripcion',
        'precio',
        'precio_oferta',
        'unidad',
        'imagen',
        'stock',
        'stock_minimo',
        'disponible',
        'destacado',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'precio_oferta' => 'decimal:2',
        'disponible' => 'boolean',
        'destacado' => 'boolean',
    ];

    /**
     * Get the tienda that owns the producto
     */
    public function tienda()
    {
        return $this->belongsTo(Tienda::class);
    }

    /**
     * Get the categoria that owns the producto
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Get the pedido items for this producto
     */
    public function pedidoItems()
    {
        return $this->hasMany(PedidoItem::class);
    }

    /**
     * Check if product is in stock
     */
    public function inStock(): bool
    {
        return $this->stock > 0 && $this->disponible;
    }

    /**
     * Check if stock is low
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->stock_minimo;
    }

    /**
     * Get the effective price (oferta or regular)
     */
    public function getPriceAttribute()
    {
        return $this->precio_oferta ?? $this->precio;
    }

    /**
     * Scope a query to only include available productos
     */
    public function scopeDisponible($query)
    {
        return $query->where('disponible', true)->where('stock', '>', 0);
    }

    /**
     * Scope a query to only include destacado productos
     */
    public function scopeDestacado($query)
    {
        return $query->where('destacado', true);
    }
}
