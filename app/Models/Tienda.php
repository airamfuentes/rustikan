<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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
        'latitud',
        'longitud',
        'valoracion',
        'total_resenas',
        'activa',
        'visible',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'visible' => 'boolean',
        'valoracion' => 'decimal:2',
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
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
     * Get the resenas for this tienda
     */
    public function resenas()
    {
        return $this->hasMany(Resena::class);
    }

    /**
     * Get the solicitudes de cambio for this tienda
     */
    public function solicitudesCambio()
    {
        return $this->hasMany(SolicitudCambio::class);
    }

    public function pedidoItems()
    {
        return $this->hasMany(PedidoItem::class);
    }

    /**
     * Usuarios que han marcado esta tienda como favorita.
     */
    public function favoritaPor()
    {
        return $this->belongsToMany(User::class, 'tienda_favoritas')
            ->withTimestamps();
    }

    /**
     * Hooks Eloquent: al borrar la tienda, eliminar también su logo y portada
     * del storage para no dejar archivos huérfanos.
     * Solo borra rutas locales (no URLs http).
     */
    protected static function booted(): void
    {
        static::deleting(function (Tienda $tienda): void {
            foreach (['logo', 'imagen_portada'] as $campo) {
                $valor = $tienda->{$campo};
                if ($valor && !str_starts_with($valor, 'http')) {
                    Storage::disk('public')->delete($valor);
                }
            }
        });
    }

    /**
     * Recalculate valoracion + total_resenas from the resenas table
     */
    public function recalcularValoracion(): void
    {
        $stats = $this->resenas()->selectRaw('COUNT(*) as total, AVG(puntuacion) as promedio')->first();
        $this->update([
            'valoracion'    => round((float) $stats->promedio, 2),
            'total_resenas' => (int) $stats->total,
        ]);
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
