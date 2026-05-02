<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'  => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'asunto'  => 'required|string|max:100',
            'mensaje' => 'required|string|max:2000',
        ]);

        Mail::raw(
            "Mensaje de contacto desde Rustikan\n\n"
            . "Nombre: {$data['nombre']}\n"
            . "Email: {$data['email']}\n"
            . "Asunto: {$data['asunto']}\n\n"
            . "Mensaje:\n{$data['mensaje']}",
            function ($msg) use ($data) {
                $msg->to('info@rustikan.com')
                    ->replyTo($data['email'], $data['nombre'])
                    ->subject("Contacto: {$data['asunto']} – {$data['nombre']}");
            }
        );

        return back()->with('success', '¡Mensaje enviado! Te responderemos en menos de 48 horas.');
    }
}
