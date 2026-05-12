<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudCambio extends Model
{
    protected $table = 'solicitudes_cambio';

    protected $fillable = [
        'user_id',
        'tienda_id',
        'producto_id',
        'tipo',
        'campo',
        'valor_anterior',
        'valor_nuevo',
        'estado',
        'motivo_rechazo',
        'revisado_por',
        'revisado_at',
    ];

    protected $casts = [
        'valor_anterior' => 'array',
        'valor_nuevo'    => 'array',
        'revisado_at'    => 'datetime',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tienda(): BelongsTo
    {
        return $this->belongsTo(Tienda::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function isAprobado(): bool
    {
        return $this->estado === 'aprobado';
    }

    public function isRechazado(): bool
    {
        return $this->estado === 'rechazado';
    }

    /**
     * Etiqueta legible del campo para mostrar en la UI.
     */
    public function labelCampo(): string
    {
        return match($this->campo) {
            'nombre'          => 'Nombre',
            'descripcion'     => 'Descripción',
            'telefono'        => 'Teléfono',
            'email'           => 'Email',
            'direccion'       => 'Dirección',
            'logo'            => 'Logo',
            'imagen_portada'  => 'Imagen de portada',
            'precio'          => 'Precio',
            'precio_oferta'   => 'Precio en oferta',
            'stock'           => 'Stock',
            'stock_minimo'    => 'Stock mínimo',
            'disponible'      => 'Disponibilidad',
            'destacado'       => 'Producto destacado',
            'oferta_activa'   => 'Oferta',
            'imagen'          => 'Imagen del producto',
            'categoria_id'    => 'Categoría',
            'unidad'          => 'Unidad de venta',
            '_oferta'         => 'Oferta (activación + precio)',
            '_nuevo_producto' => 'Nuevo producto',
            '_eliminar'       => 'Eliminar producto',
            default           => $this->campo ?? $this->tipo,
        };
    }
}
