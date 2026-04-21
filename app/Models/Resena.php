<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Resena extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tienda_id',
        'puntuacion',
        'titulo',
        'comentario',
    ];

    protected $casts = [
        'puntuacion' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class);
    }
}
