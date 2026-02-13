<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tienda extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'categoria_id',
        'nombre',
        'slug',
        'descripcion',
        'logo',
        'imagen_portada',
        'telefono',
        'email',
        'direccion',
        'valoracion',
        'total_resenas',
        'activa',
        'visible',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'visible' => 'boolean',
        'valoracion' => 'decimal:2',
    ];

    /**
     * Get the owner of the tienda
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category of the tienda
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Get the productos in this tienda
     */
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    /**
     * Scope a query to only include active tiendas
     */
    public function scopeActiva($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Scope a query to only include visible tiendas
     */
    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }
}
