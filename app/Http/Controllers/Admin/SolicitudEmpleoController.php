<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\EmpleoRechazada;
use App\Models\ActivityLog;
use App\Models\SolicitudEmpleo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SolicitudEmpleoController extends Controller
{
    /**
     * Listado paginado con filtros por estado.
     */
    public function index(Request $request)
    {
        $estado = $request->query('estado', 'pendiente');

        $solicitudes = SolicitudEmpleo::where('estado', $estado)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (SolicitudEmpleo $s) => [
                'id'                  => $s->id,
                'nombre'              => $s->nombre,
                'apellidos'           => $s->apellidos,
                'email'               => $s->email,
                'telefono'            => $s->telefono,
                'puesto'              => $s->puesto,
                'puesto_label'        => $this->etiquetaPuesto($s->puesto),
                'mensaje'             => $s->mensaje,
                'cv_path'             => $s->cv_path,
                'cv_nombre_original'  => $s->cv_nombre_original,
                'estado'              => $s->estado,
                'created_at'          => $s->created_at->format('d/m/Y H:i'),
            ]);

        $counts = [
            'pendiente'  => SolicitudEmpleo::where('estado', 'pendiente')->count(),
            'revisada'   => SolicitudEmpleo::where('estado', 'revisada')->count(),
            'contactada' => SolicitudEmpleo::where('estado', 'contactada')->count(),
            'rechazada'  => SolicitudEmpleo::where('estado', 'rechazada')->count(),
        ];

        return Inertia::render('Admin/SolicitudesEmpleo/Index', [
            'solicitudes' => $solicitudes,
            'estado'      => $estado,
            'counts'      => $counts,
        ]);
    }

    /**
     * Cambiar el estado de una solicitud (revisada / contactada / rechazada / pendiente).
     * Si la transición es a "rechazada", se envía email automático al candidato
     * (solo la primera vez que pasa a rechazada, no en re-rechazos).
     */
    public function updateEstado(Request $request, SolicitudEmpleo $solicitud)
    {
        $data = $request->validate([
            'estado' => 'required|in:pendiente,revisada,contactada,rechazada',
        ]);

        $anterior = $solicitud->estado;
        $solicitud->update(['estado' => $data['estado']]);

        ActivityLog::log(
            'solicitud_empleo_estado',
            "Solicitud de empleo de {$solicitud->nombre} {$solicitud->apellidos}: {$anterior} → {$data['estado']}",
            'editar',
            'blue',
            $solicitud
        );

        $mailEnviado = false;

        // Solo enviar email si el estado nuevo es "rechazada" Y antes NO lo era
        if ($data['estado'] === 'rechazada' && $anterior !== 'rechazada') {
            $puestoLabel = \App\Http\Controllers\TrabajaConNosotrosController::PUESTOS[$solicitud->puesto]
                ?? $solicitud->puesto;
            try {
                Mail::to($solicitud->email)
                    ->send(new EmpleoRechazada($solicitud, $puestoLabel));
                $mailEnviado = true;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $msg = 'Estado actualizado.';
        if ($mailEnviado) {
            $msg .= ' Se ha enviado el email de notificación al candidato.';
        }

        return back()->with('success', $msg);
    }

    /**
     * Eliminar la solicitud (el modelo borrará el CV físico vía hook deleting).
     */
    public function destroy(SolicitudEmpleo $solicitud)
    {
        $nombre = "{$solicitud->nombre} {$solicitud->apellidos}";
        $solicitud->delete();

        ActivityLog::log(
            'solicitud_empleo_eliminada',
            "Solicitud de empleo eliminada: {$nombre}",
            'eliminar',
            'red'
        );

        return back()->with('success', 'Solicitud eliminada.');
    }

    /**
     * Descargar el CV adjunto de una solicitud.
     */
    public function downloadCv(SolicitudEmpleo $solicitud): BinaryFileResponse
    {
        abort_unless($solicitud->cv_path, 404, 'Esta solicitud no tiene CV adjunto.');

        $absoluta = Storage::disk('public')->path($solicitud->cv_path);
        abort_unless(is_file($absoluta), 404, 'El archivo CV no existe en el servidor.');

        $nombre = $solicitud->cv_nombre_original ?: basename($solicitud->cv_path);

        return response()->download($absoluta, $nombre);
    }

    private function etiquetaPuesto(string $key): string
    {
        return [
            'desarrollo'        => 'Desarrollo / Tecnología',
            'atencion-cliente'  => 'Atención al cliente',
            'marketing'         => 'Marketing y comunicación',
            'logistica'         => 'Operaciones / Logística',
            'reparto'           => 'Repartidor',
            'otro'              => 'Otro',
        ][$key] ?? $key;
    }
}
