<?php

namespace App\Http\Controllers;

use App\Mail\EmpleoRecibida;
use App\Models\SolicitudEmpleo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class TrabajaConNosotrosController extends Controller
{
    /** Etiquetas legibles de los puestos disponibles. */
    public const PUESTOS = [
        'desarrollo'        => 'Desarrollo / Tecnología',
        'atencion-cliente'  => 'Atención al cliente',
        'marketing'         => 'Marketing y comunicación',
        'logistica'         => 'Operaciones / Logística',
        'reparto'           => 'Repartidor',
        'otro'              => 'Otro',
    ];

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'    => 'required|string|min:2|max:100|regex:/^[\pL\s\'-]+$/u',
            'apellidos' => 'required|string|min:2|max:100|regex:/^[\pL\s\'-]+$/u',
            'email'     => 'required|email|max:180',
            'telefono'  => 'nullable|string|max:20',
            'puesto'    => 'required|string|max:120|in:' . implode(',', array_keys(self::PUESTOS)),
            'mensaje'   => 'required|string|min:30|max:2000',
            // CV: PDF, DOC, DOCX, máximo 5 MB
            'cv'        => 'required|file|mimes:pdf,doc,docx|max:5120',
        ], [
            'nombre.regex'    => 'El nombre solo puede contener letras.',
            'apellidos.regex' => 'Los apellidos solo pueden contener letras.',
            'puesto.in'       => 'El puesto seleccionado no es válido.',
            'cv.mimes'        => 'El CV debe ser un archivo PDF, DOC o DOCX.',
            'cv.max'          => 'El CV no puede superar los 5 MB.',
        ]);

        // Guardar CV en storage/app/public/cvs
        $cvOriginal = $request->file('cv')->getClientOriginalName();
        $cvPath     = $request->file('cv')->store('cvs', 'public');

        $solicitud = SolicitudEmpleo::create([
            'nombre'             => $data['nombre'],
            'apellidos'          => $data['apellidos'],
            'email'              => $data['email'],
            'telefono'           => $data['telefono'] ?? null,
            'puesto'             => $data['puesto'],
            'mensaje'            => $data['mensaje'],
            'cv_path'            => $cvPath,
            'cv_nombre_original' => $cvOriginal,
        ]);

        $puestoLabel = self::PUESTOS[$solicitud->puesto] ?? $solicitud->puesto;

        // 1) Notificar al equipo por email con el CV adjunto
        try {
            Mail::raw(
                "Nueva solicitud de empleo\n\n"
                . "Nombre:    {$solicitud->nombre} {$solicitud->apellidos}\n"
                . "Email:     {$solicitud->email}\n"
                . "Teléfono:  " . ($solicitud->telefono ?: '—') . "\n"
                . "Puesto:    {$puestoLabel}\n\n"
                . "Mensaje:\n{$solicitud->mensaje}\n\n"
                . "CV adjunto."
                ,
                function ($msg) use ($solicitud, $puestoLabel) {
                    $msg->to('info@rustikan.com')
                        ->replyTo($solicitud->email, "{$solicitud->nombre} {$solicitud->apellidos}")
                        ->subject("Solicitud de empleo: {$puestoLabel} – {$solicitud->nombre}")
                        ->attach(Storage::disk('public')->path($solicitud->cv_path), [
                            'as'   => $solicitud->cv_nombre_original,
                        ]);
                }
            );
        } catch (\Throwable $e) {
            // No bloquear al usuario si el envío de mail interno falla; queda en BD.
            report($e);
        }

        // 2) Enviar email de confirmación al candidato
        try {
            Mail::to($solicitud->email)
                ->send(new EmpleoRecibida($solicitud, $puestoLabel));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', '¡Solicitud enviada! Te hemos enviado un email de confirmación. Revisaremos tu CV y te contactaremos en breve.');
    }
}
