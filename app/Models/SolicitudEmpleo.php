<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SolicitudEmpleo extends Model
{
    protected $table = 'solicitudes_empleo';

    protected $fillable = [
        'nombre', 'apellidos', 'email', 'telefono',
        'puesto', 'mensaje',
        'cv_path', 'cv_nombre_original',
        'estado',
    ];

    /**
     * Al borrar la solicitud, borra también el CV físico.
     */
    protected static function booted(): void
    {
        static::deleting(function (SolicitudEmpleo $s): void {
            if ($s->cv_path && !str_starts_with($s->cv_path, 'http')) {
                Storage::disk('public')->delete($s->cv_path);
            }
        });
    }
}
