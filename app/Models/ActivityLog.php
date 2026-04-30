<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'tipo',
        'descripcion',
        'icono',
        'color',
        'user_id',
        'model_type',
        'model_id',
        'datos',
    ];

    protected $casts = [
        'datos' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relación con el usuario que realizó la acción
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación polimórfica con el modelo afectado
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Helper estático para registrar actividad rápidamente
     */
    public static function log($tipo, $descripcion, $icono = 'editar', $color = 'gray', $model = null, $datos = null)
    {
        return self::create([
            'tipo' => $tipo,
            'descripcion' => $descripcion,
            'icono' => $icono,
            'color' => $color,
            'user_id' => auth()->id(),
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'datos' => $datos,
        ]);
    }
}
