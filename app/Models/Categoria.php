<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'slug',
        'icono',
        'descripcion',
    ];

    /**
     * Get the tiendas in this category
     */
    public function tiendas()
    {
        return $this->hasMany(Tienda::class);
    }
}
