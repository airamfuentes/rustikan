<?php

namespace App\Http\Controllers;

use App\Models\MensajeChat;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Http\Request;

class ChatAdminSupplierController extends Controller
{
    public function getMensajes(Request $request)
    {
        $user = $request->user();

        if ($user->isSupplier()) {
            $mensajes = MensajeChat::where('supplier_id', $user->id)
                ->with('remitente:id,name,role,avatar')
                ->orderBy('created_at', 'asc')
                ->get();

            // Marcar mensajes de admin como leídos por el supplier
            MensajeChat::where('supplier_id', $user->id)
                ->where('remitente_id', '!=', $user->id)
                ->where('leido_supplier', false)
                ->update(['leido_supplier' => true]);

            return response()->json(['mensajes' => $mensajes]);
        }

        if ($user->isAdmin()) {
            $supplierId = $request->query('supplier_id');
            $mensajes = MensajeChat::where('supplier_id', $supplierId)
                ->with('remitente:id,name,role,avatar')
                ->orderBy('created_at', 'asc')
                ->get();

            // Marcar mensajes del supplier como leídos por admin
            MensajeChat::where('supplier_id', $supplierId)
                ->where('remitente_id', $supplierId)
                ->where('leido_admin', false)
                ->update(['leido_admin' => true]);

            return response()->json(['mensajes' => $mensajes]);
        }

        abort(403);
    }

    public function enviar(Request $request)
    {
        $user = $request->user();
        $request->validate(['mensaje' => 'required|string|max:2000']);

        if ($user->isSupplier()) {
            $msg = MensajeChat::create([
                'supplier_id'    => $user->id,
                'remitente_id'   => $user->id,
                'mensaje'        => $request->mensaje,
                'leido_admin'    => false,
                'leido_supplier' => true,
            ]);

            Notificacion::enviarAdmins(
                'chat_supplier',
                'Mensaje de almacén',
                "{$user->name} te ha enviado un mensaje.",
                null,
                'message-circle',
                'blue'
            );
        } elseif ($user->isAdmin()) {
            $request->validate(['supplier_id' => 'required|exists:users,id']);
            $msg = MensajeChat::create([
                'supplier_id'    => $request->supplier_id,
                'remitente_id'   => $user->id,
                'mensaje'        => $request->mensaje,
                'leido_admin'    => true,
                'leido_supplier' => false,
            ]);

            Notificacion::enviar(
                $request->supplier_id,
                'chat_admin',
                'Mensaje del equipo Rustikan',
                'El equipo de administración te ha enviado un mensaje.',
                null,
                'message-circle',
                'primary'
            );
        } else {
            abort(403);
        }

        return response()->json([
            'ok'      => true,
            'mensaje' => $msg->load('remitente:id,name,role,avatar'),
        ]);
    }

    public function conversaciones(Request $request)
    {
        $request->user()->isAdmin() || abort(403);

        $suppliers = User::where('role', 'supplier')->get(['id', 'name', 'avatar', 'email']);

        $data = $suppliers->map(function ($supplier) {
            $ultimo = MensajeChat::where('supplier_id', $supplier->id)
                ->orderByDesc('created_at')
                ->first(['mensaje', 'created_at', 'remitente_id']);

            $noLeidos = MensajeChat::where('supplier_id', $supplier->id)
                ->where('remitente_id', $supplier->id)
                ->where('leido_admin', false)
                ->count();

            return [
                'supplier'       => $supplier,
                'ultimo_mensaje' => $ultimo?->mensaje,
                'ultimo_at'      => $ultimo?->created_at,
                'no_leidos'      => $noLeidos,
            ];
        })->sortByDesc('ultimo_at')->values();

        return response()->json(['conversaciones' => $data]);
    }

    public function noLeidos(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['count' => 0]);

        if ($user->isSupplier()) {
            $count = MensajeChat::where('supplier_id', $user->id)
                ->where('remitente_id', '!=', $user->id)
                ->where('leido_supplier', false)
                ->count();
        } elseif ($user->isAdmin()) {
            $count = MensajeChat::whereColumn('remitente_id', 'supplier_id')
                ->where('leido_admin', false)
                ->count();
        } else {
            $count = 0;
        }

        return response()->json(['count' => $count]);
    }
}
