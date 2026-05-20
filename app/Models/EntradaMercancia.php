<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntradaMercancia extends Model
{
    protected $fillable = [
        'producto_id',
        'tienda_id',
        'usuario_id',
        'stock_anterior',
        'cantidad_entrada',
        'stock_nuevo',
        'numero_documento',
        'proveedor',
        'notas',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
