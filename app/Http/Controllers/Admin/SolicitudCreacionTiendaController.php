<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SolicitudCreacionTienda;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SolicitudCreacionTiendaController extends Controller
{
    public function index(Request $request)
    {
        $estado = $request->query('estado', 'pendiente');

        $solicitudes = SolicitudCreacionTienda::where('estado', $estado)
            ->with(['user:id,name,email', 'revisor:id,name'])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($s) => [
                'id'                   => $s->id,
                'nombre_tienda'        => $s->nombre_tienda,
                'nombre_contacto'      => $s->nombre_contacto,
                'email'                => $s->email,
                'telefono'             => $s->telefono,
                'categoria'            => $s->categoria,
                'descripcion'          => $s->descripcion,
                'municipio'            => $s->municipio,
                'direccion'            => $s->direccion,
                'web'                  => $s->web,
                'instagram'            => $s->instagram,
                'productos_descripcion'=> $s->productos_descripcion,
                'estado'               => $s->estado,
                'notas_admin'          => $s->notas_admin,
                'revisado_por_nombre'  => $s->revisor?->name,
                'created_at'           => $s->created_at->format('d/m/Y H:i'),
                'user_nombre'          => $s->user?->name,
            ]);

        $counts = [
            'pendiente' => SolicitudCreacionTienda::where('estado', 'pendiente')->count(),
            'aprobada'  => SolicitudCreacionTienda::where('estado', 'aprobada')->count(),
            'rechazada' => SolicitudCreacionTienda::where('estado', 'rechazada')->count(),
        ];

        return Inertia::render('Admin/SolicitudesCreacion/Index', [
            'solicitudes' => $solicitudes,
            'estado'      => $estado,
            'counts'      => $counts,
        ]);
    }

    public function aprobar(Request $request, SolicitudCreacionTienda $solicitud)
    {
        /** @var SolicitudCreacionTienda $solicitud */
        if (!$solicitud->isPendiente()) {
            return back()->with('error', 'Esta solicitud ya fue revisada.');
        }

        $solicitud->update([
            'estado'       => 'aprobada',
            'notas_admin'  => $request->input('notas'),
            'revisado_por' => auth()->id(),
            'revisado_at'  => now(),
        ]);

        return back()->with('success', "Solicitud de \"{$solicitud->nombre_tienda}\" aprobada.");
    }

    public function rechazar(Request $request, SolicitudCreacionTienda $solicitud)
    {
        /** @var SolicitudCreacionTienda $solicitud */
        if (!$solicitud->isPendiente()) {
            return back()->with('error', 'Esta solicitud ya fue revisada.');
        }

        $request->validate(['notas' => 'nullable|string|max:500']);

        $solicitud->update([
            'estado'       => 'rechazada',
            'notas_admin'  => $request->input('notas'),
            'revisado_por' => auth()->id(),
            'revisado_at'  => now(),
        ]);

        return back()->with('success', "Solicitud de \"{$solicitud->nombre_tienda}\" rechazada.");
    }
}
