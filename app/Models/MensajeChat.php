<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MensajeChat extends Model
{
    protected $table = 'mensajes_chat';

    protected $fillable = [
        'supplier_id',
        'remitente_id',
        'mensaje',
        'leido_admin',
        'leido_supplier',
    ];

    protected $casts = [
        'leido_admin'    => 'boolean',
        'leido_supplier' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function remitente()
    {
        return $this->belongsTo(User::class, 'remitente_id');
    }
}
