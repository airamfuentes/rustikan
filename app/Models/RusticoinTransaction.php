<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RusticoinTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'tipo',
        'cantidad',
        'saldo_despues',
        'descripcion',
        'pedido_id',
    ];

    protected $casts = [
        'cantidad'      => 'decimal:2',
        'saldo_despues' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
