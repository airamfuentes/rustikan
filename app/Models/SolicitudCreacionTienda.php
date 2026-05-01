<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudCreacionTienda extends Model
{
    protected $table = 'solicitudes_creacion_tienda';

    protected $fillable = [
        'user_id',
        'nombre_tienda',
        'nombre_contacto',
        'email',
        'telefono',
        'categoria',
        'descripcion',
        'municipio',
        'direccion',
        'web',
        'instagram',
        'productos_descripcion',
        'estado',
        'notas_admin',
        'revisado_por',
        'revisado_at',
    ];

    protected $casts = [
        'revisado_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function isPendiente(): bool { return $this->estado === 'pendiente'; }
    public function isAprobada(): bool  { return $this->estado === 'aprobada'; }
    public function isRechazada(): bool { return $this->estado === 'rechazada'; }
}
