<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Notificacion;
use App\Models\Producto;
use App\Models\SolicitudCambio;
use App\Models\Tienda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SolicitudController extends Controller
{
    /**
     * Vista principal: todas las solicitudes pendientes agrupadas por tienda.
     */
    public function index(Request $request)
    {
        $estado = $request->get('estado', 'pendiente');

        // Tiendas que tienen solicitudes en el estado solicitado
        $tiendas = Tienda::whereHas('solicitudesCambio', fn($q) => $q->where('estado', $estado))
            ->with([
                'solicitudesCambio' => function ($q) use ($estado) {
                    $q->where('estado', $estado)
                      ->with(['producto:id,nombre,imagen', 'user:id,name'])
                      ->orderBy('created_at');
                },
                'user:id,name,email',
            ])
            ->get()
            ->map(fn($tienda) => [
                'id'     => $tienda->id,
                'nombre' => $tienda->nombre,
                'logo'   => $tienda->logo,
                'owner'  => $tienda->user?->name,
                'total'  => $tienda->solicitudesCambio->count(),
                'solicitudes' => $tienda->solicitudesCambio->map(fn($s) => $this->formatSolicitud($s)),
            ]);

        $counts = [
            'pendiente' => SolicitudCambio::where('estado', 'pendiente')->count(),
            'aprobado'  => SolicitudCambio::where('estado', 'aprobado')->count(),
            'rechazado' => SolicitudCambio::where('estado', 'rechazado')->count(),
        ];

        return Inertia::render('Admin/Solicitudes/Index', [
            'tiendas' => $tiendas,
            'estado'  => $estado,
            'counts'  => $counts,
        ]);
    }

    /**
     * Aprobar una solicitud individual y aplicar el cambio.
     */
    public function aprobar(SolicitudCambio $solicitud)
    {
        if (!$solicitud->isPendiente()) {
            return back()->with('error', 'Esta solicitud ya fue revisada.');
        }

        $this->aplicarCambio($solicitud);

        $solicitud->update([
            'estado'       => 'aprobado',
            'revisado_por' => auth()->id(),
            'revisado_at'  => now(),
        ]);

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'tipo'        => 'aprobar_solicitud',
            'descripcion' => "Aprobado: {$solicitud->labelCampo()} (tienda #{$solicitud->tienda_id})",
            'icono'       => 'aprobada',
            'color'       => 'green',
            'model_type'  => 'SolicitudCambio',
            'model_id'    => $solicitud->id,
        ]);

        // Notificar al owner
        if ($solicitud->user_id) {
            Notificacion::enviar(
                $solicitud->user_id,
                'solicitud_aprobada',
                'Cambio aprobado',
                "Tu solicitud de cambio en \"{$solicitud->labelCampo()}\" ha sido aprobada.",
                route('owner.panel'),
                'check',
                'green'
            );
        }

        return back()->with('success', "Cambio de \"{$solicitud->labelCampo()}\" aprobado y aplicado.");
    }

    /**
     * Rechazar una solicitud individual.
     */
    public function rechazar(Request $request, SolicitudCambio $solicitud)
    {
        if (!$solicitud->isPendiente()) {
            return back()->with('error', 'Esta solicitud ya fue revisada.');
        }

        $request->validate([
            'motivo' => 'nullable|string|max:500',
        ]);

        $solicitud->update([
            'estado'          => 'rechazado',
            'motivo_rechazo'  => $request->motivo,
            'revisado_por'    => auth()->id(),
            'revisado_at'     => now(),
        ]);

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'tipo'        => 'rechazar_solicitud',
            'descripcion' => "Rechazado: {$solicitud->labelCampo()} (tienda #{$solicitud->tienda_id})",
            'icono'       => 'rechazada',
            'color'       => 'red',
            'model_type'  => 'SolicitudCambio',
            'model_id'    => $solicitud->id,
        ]);

        // Notificar al owner
        if ($solicitud->user_id) {
            $motivoTexto = $request->motivo ? ": {$request->motivo}" : '.';
            Notificacion::enviar(
                $solicitud->user_id,
                'solicitud_rechazada',
                'Cambio rechazado',
                "Tu solicitud de cambio en \"{$solicitud->labelCampo()}\" ha sido rechazada{$motivoTexto}",
                route('owner.panel'),
                'x',
                'red'
            );
        }

        return back()->with('success', "Cambio de \"{$solicitud->labelCampo()}\" rechazado.");
    }

    /**
     * Aprobar todas las solicitudes pendientes de una tienda.
     */
    public function aprobarTodas(Tienda $tienda)
    {
        $solicitudes = SolicitudCambio::where('tienda_id', $tienda->id)
            ->where('estado', 'pendiente')
            ->get();

        if ($solicitudes->isEmpty()) {
            return back()->with('info', 'No hay solicitudes pendientes para esta tienda.');
        }

        foreach ($solicitudes as $solicitud) {
            $this->aplicarCambio($solicitud);
            $solicitud->update([
                'estado'       => 'aprobado',
                'revisado_por' => auth()->id(),
                'revisado_at'  => now(),
            ]);
        }

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'tipo'        => 'aprobar_todas_solicitudes',
            'descripcion' => "Aprobadas {$solicitudes->count()} solicitudes de {$tienda->nombre}",
            'icono'       => 'aprobada',
            'color'       => 'green',
            'model_type'  => 'Tienda',
            'model_id'    => $tienda->id,
        ]);

        return back()->with('success', "Se aprobaron y aplicaron {$solicitudes->count()} cambios de {$tienda->nombre}.");
    }

    /**
     * Rechazar todas las solicitudes pendientes de una tienda.
     */
    public function rechazarTodas(Request $request, Tienda $tienda)
    {
        $request->validate(['motivo' => 'nullable|string|max:500']);

        $count = SolicitudCambio::where('tienda_id', $tienda->id)
            ->where('estado', 'pendiente')
            ->update([
                'estado'         => 'rechazado',
                'motivo_rechazo' => $request->motivo,
                'revisado_por'   => auth()->id(),
                'revisado_at'    => now(),
            ]);

        if ($count === 0) {
            return back()->with('info', 'No hay solicitudes pendientes.');
        }

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'tipo'        => 'rechazar_todas_solicitudes',
            'descripcion' => "Rechazadas {$count} solicitudes de {$tienda->nombre}",
            'icono'       => 'rechazada',
            'color'       => 'red',
            'model_type'  => 'Tienda',
            'model_id'    => $tienda->id,
        ]);

        return back()->with('success', "Se rechazaron {$count} cambios de {$tienda->nombre}.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Aplicar el cambio al modelo correspondiente.
     */
    private function aplicarCambio(SolicitudCambio $solicitud): void
    {
        $nuevoValor = $solicitud->valor_nuevo['value'] ?? $solicitud->valor_nuevo ?? null;

        switch ($solicitud->tipo) {
            case 'update_tienda':
                $tienda = $solicitud->tienda;
                if (!$tienda) return;

                // Si se reemplaza una imagen que antes estaba en disco, borrarla
                $anteriorValor = $solicitud->valor_anterior['value'] ?? null;
                if (in_array($solicitud->campo, ['logo', 'imagen_portada'])
                    && $anteriorValor
                    && !str_starts_with($anteriorValor, 'http')
                    && !str_starts_with((string)$nuevoValor, 'http')
                ) {
                    Storage::disk('public')->delete($anteriorValor);
                }

                // Mover imagen de staging al directorio definitivo
                $nuevoValor = $this->moverStagingImagen($solicitud->campo, $nuevoValor, 'tiendas');

                $tienda->update([$solicitud->campo => $nuevoValor]);
                break;

            case 'create_producto':
                $datos = $solicitud->valor_nuevo;
                if (!$datos || !$solicitud->tienda) return;

                // Mover imagen de staging
                if (!empty($datos['imagen'])) {
                    $datos['imagen'] = $this->moverStagingImagen('imagen', $datos['imagen'], 'productos');
                }

                Producto::create(array_merge($datos, [
                    'tienda_id'  => $solicitud->tienda_id,
                    'slug'       => Str::slug($datos['nombre']) . '-' . Str::random(5),
                    'disponible' => true,
                    'destacado'  => false,
                ]));
                break;

            case 'update_producto':
                $producto = $solicitud->producto;
                if (!$producto) return;

                // Campo compuesto: solicitud de oferta (oferta_activa + precio_oferta).
                if ($solicitud->campo === '_oferta') {
                    $nuevo = $solicitud->valor_nuevo ?? [];
                    $activa = (bool) ($nuevo['oferta_activa'] ?? false);
                    $producto->update([
                        'oferta_activa' => $activa,
                        // Al desactivar la oferta limpiamos el precio especial.
                        'precio_oferta' => $activa ? ($nuevo['precio_oferta'] ?? null) : null,
                    ]);
                    break;
                }

                $anteriorValor = $solicitud->valor_anterior['value'] ?? null;
                if ($solicitud->campo === 'imagen'
                    && $anteriorValor
                    && !str_starts_with($anteriorValor, 'http')
                ) {
                    Storage::disk('public')->delete($anteriorValor);
                }

                $nuevoValor = $this->moverStagingImagen($solicitud->campo, $nuevoValor, 'productos');

                if ($solicitud->campo === 'nombre') {
                    $producto->update([
                        'nombre' => $nuevoValor,
                        'slug'   => Str::slug($nuevoValor) . '-' . Str::random(5),
                    ]);
                } else {
                    $producto->update([$solicitud->campo => $nuevoValor]);
                }
                break;

            case 'delete_producto':
                $producto = $solicitud->producto;
                if (!$producto) return;

                if ($producto->imagen && !str_starts_with($producto->imagen, 'http')) {
                    Storage::disk('public')->delete($producto->imagen);
                }
                $producto->delete();
                break;
        }
    }

    /**
     * Mueve un archivo de staging al directorio definitivo.
     * Devuelve la ruta definitiva (o el valor original si no aplica).
     */
    private function moverStagingImagen(string $campo, mixed $valor, string $dirBase): mixed
    {
        if (!is_string($valor)) return $valor;
        if (!in_array($campo, ['logo', 'imagen_portada', 'imagen'])) return $valor;
        if (str_starts_with($valor, 'http')) return $valor;
        if (!str_starts_with($valor, 'solicitudes/')) return $valor;

        $ext      = pathinfo($valor, PATHINFO_EXTENSION);
        $destino  = "{$dirBase}/" . Str::random(20) . ".{$ext}";

        if (Storage::disk('public')->exists($valor)) {
            Storage::disk('public')->move($valor, $destino);
            return $destino;
        }

        return $valor;
    }

    /**
     * Formatea una solicitud para enviar al frontend.
     */
    private function formatSolicitud(SolicitudCambio $s): array
    {
        return [
            'id'             => $s->id,
            'tipo'           => $s->tipo,
            'campo'          => $s->campo,
            'label_campo'    => $s->labelCampo(),
            'valor_anterior' => $s->valor_anterior,
            'valor_nuevo'    => $s->valor_nuevo,
            'estado'         => $s->estado,
            'motivo_rechazo' => $s->motivo_rechazo,
            'created_at'     => $s->created_at->format('d/m/Y H:i'),
            'created_at_iso' => $s->created_at->toIso8601String(),
            'solicitante'    => $s->user?->name,
            'producto'       => $s->producto
                ? ['id' => $s->producto->id, 'nombre' => $s->producto->nombre, 'imagen' => $s->producto->imagen]
                : null,
        ];
    }
}
