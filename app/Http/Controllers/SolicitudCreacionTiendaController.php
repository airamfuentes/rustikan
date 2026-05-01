<?php

namespace App\Http\Controllers;

use App\Mail\SolicitudRecibida;
use App\Models\SolicitudCreacionTienda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SolicitudCreacionTiendaController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_tienda'        => 'required|string|max:120',
            'nombre_contacto'      => 'required|string|max:120',
            'email'                => 'required|email|max:180',
            'telefono'             => 'nullable|string|max:20',
            'categoria'            => 'required|string|max:80',
            'descripcion'          => 'required|string|min:20|max:1000',
            'municipio'            => 'nullable|string|max:80',
            'direccion'            => 'nullable|string|max:200',
            'web'                  => 'nullable|url|max:200',
            'instagram'            => 'nullable|string|max:80',
            'productos_descripcion'=> 'required|string|min:20|max:1000',
        ]);

        $data['user_id'] = auth()->id();

        $solicitud = SolicitudCreacionTienda::create($data);

        // Enviar email de confirmación al solicitante
        try {
            Mail::to($data['email'])->send(new SolicitudRecibida($solicitud));
        } catch (\Throwable) {
            // El fallo del email no debe bloquear la solicitud
        }

        return back()
            ->with('solicitud_enviada', true)
            ->with('success', '¡Solicitud enviada! Te hemos enviado un email de confirmación.');
    }
}
