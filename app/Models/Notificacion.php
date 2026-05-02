<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'user_id',
        'tipo',
        'titulo',
        'mensaje',
        'enlace',
        'icono',
        'color',
        'leida',
    ];

    protected $casts = [
        'leida' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Crear una notificación para un usuario.
     */
    public static function enviar(int $userId, string $tipo, string $titulo, string $mensaje, ?string $enlace = null, string $icono = 'bell', string $color = 'primary'): self
    {
        return static::create([
            'user_id' => $userId,
            'tipo'    => $tipo,
            'titulo'  => $titulo,
            'mensaje' => $mensaje,
            'enlace'  => $enlace,
            'icono'   => $icono,
            'color'   => $color,
            'leida'   => false,
        ]);
    }

    /**
     * Enviar a todos los admins.
     */
    public static function enviarAdmins(string $tipo, string $titulo, string $mensaje, ?string $enlace = null, string $icono = 'bell', string $color = 'primary'): void
    {
        $admins = User::where('role', 'admin')->pluck('id');
        foreach ($admins as $adminId) {
            static::enviar($adminId, $tipo, $titulo, $mensaje, $enlace, $icono, $color);
        }
    }
}
