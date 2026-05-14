<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Notificacion;
use App\Models\Producto;
use App\Models\SolicitudCambio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SolicitudController extends Controller
{
    // ── Campos de tienda que requieren aprobación ─────────────────────────────
    private const CAMPOS_TIENDA = [
        'nombre', 'categoria_id', 'descripcion', 'telefono', 'email',
        'direccion', 'logo', 'imagen_portada',
    ];

    // ── Todos los campos de producto requieren aprobación ─────────────────────
    private const CAMPOS_PRODUCTO = [
        'nombre', 'descripcion', 'precio', 'precio_oferta',
        'oferta_activa', 'unidad', 'stock', 'stock_minimo',
        'disponible', 'destacado', 'imagen', 'categoria_id',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // TIENDA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Solicitar cambios en la tienda (cada campo modificado = 1 solicitud).
     */
    public function solicitarCambioTienda(Request $request)
    {
        $user   = auth()->user();
        $tienda = $user->tiendas()->firstOrFail();

        $validated = $request->validate([
            'nombre'         => 'required|string|max:255',
            'categoria_id'   => 'required|exists:categorias,id',
            'descripcion'    => 'nullable|string|max:5000',
            'telefono'       => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'direccion'      => 'nullable|string|max:500',
            'logo'           => 'nullable|image|max:3072',
            'logo_url'       => 'nullable|url|max:2048',
            'imagen_portada' => 'nullable|image|max:3072',
            'imagen_portada_url' => 'nullable|url|max:2048',
        ]);

        // Procesar imágenes si se envían (se guardan en staging y se incluyen en la solicitud)
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('solicitudes/logos', 'public');
        } elseif ($request->filled('logo_url')) {
            $validated['logo'] = $request->input('logo_url');
        } else {
            unset($validated['logo']);
        }

        if ($request->hasFile('imagen_portada')) {
            $validated['imagen_portada'] = $request->file('imagen_portada')->store('solicitudes/portadas', 'public');
        } elseif ($request->filled('imagen_portada_url')) {
            $validated['imagen_portada'] = $request->input('imagen_portada_url');
        } else {
            unset($validated['imagen_portada']);
        }

        $cambiosCreados = 0;

        foreach (self::CAMPOS_TIENDA as $campo) {
            if (!array_key_exists($campo, $validated)) {
                continue;
            }

            $valorNuevo    = $validated[$campo];
            $valorAnterior = $tienda->$campo;

            // Solo crear solicitud si realmente cambia algo
            if ($valorNuevo == $valorAnterior) {
                continue;
            }

            // Evitar solicitudes duplicadas pendientes para el mismo campo
            SolicitudCambio::where('tienda_id', $tienda->id)
                ->where('campo', $campo)
                ->where('estado', 'pendiente')
                ->whereNull('producto_id')
                ->delete();

            SolicitudCambio::create([
                'user_id'        => $user->id,
                'tienda_id'      => $tienda->id,
                'tipo'           => 'update_tienda',
                'campo'          => $campo,
                'valor_anterior' => ['value' => $valorAnterior],
                'valor_nuevo'    => ['value' => $valorNuevo],
            ]);

            $cambiosCreados++;
        }

        if ($cambiosCreados === 0) {
            return back()->with('info', 'No se detectaron cambios respecto a los datos actuales.');
        }

        // Notificar a los admins
        Notificacion::enviarAdmins(
            'nueva_solicitud_tienda',
            'Nueva solicitud de cambio en tienda',
            "La tienda \"{$tienda->nombre}\" ha enviado {$cambiosCreados} solicitud(es) de cambio.",
            route('admin.solicitudes.index'),
            'store',
            'orange'
        );

        return back()->with('success', "Se ha enviado la solicitud con {$cambiosCreados} " . ($cambiosCreados === 1 ? 'cambio' : 'cambios') . " al administrador para su revisión.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRODUCTOS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Solicitar creación de un nuevo producto.
     */
    public function solicitarCrearProducto(Request $request)
    {
        $user   = auth()->user();
        $tienda = $user->tiendas()->firstOrFail();

        $validated = $request->validate([
            'nombre'        => 'required|string|max:255',
            'categoria_id'  => 'required|exists:categorias,id',
            'descripcion'   => 'nullable|string|max:3000',
            'precio'        => 'required|numeric|min:0|max:99999',
            'precio_oferta' => 'nullable|numeric|min:0',
            'unidad'        => 'required|string|max:30',
            'stock'         => 'required|integer|min:0',
            'imagen'        => 'nullable|image|max:3072',
            'imagen_url'    => 'nullable|url|max:2048',
        ]);

        // Subir imagen de staging si viene
        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $request->file('imagen')->store('solicitudes/productos', 'public');
        } elseif ($request->filled('imagen_url')) {
            $validated['imagen'] = $request->input('imagen_url');
        }
        unset($validated['imagen_url']);

        SolicitudCambio::create([
            'user_id'        => $user->id,
            'tienda_id'      => $tienda->id,
            'tipo'           => 'create_producto',
            'campo'          => '_nuevo_producto',
            'valor_anterior' => null,
            'valor_nuevo'    => $validated,
        ]);

        return back()->with('success', 'Solicitud de nuevo producto enviada. El administrador la revisará en breve.');
    }

    /**
     * Solicitar cambios en un producto existente.
     */
    public function solicitarEditarProducto(Request $request, Producto $producto)
    {
        $user   = auth()->user();
        $tienda = $user->tiendas()->firstOrFail();

        if ($producto->tienda_id !== $tienda->id) {
            abort(403);
        }

        $validated = $request->validate([
            'nombre'        => 'required|string|max:255',
            'categoria_id'  => 'required|exists:categorias,id',
            'descripcion'   => 'nullable|string|max:3000',
            'precio'        => 'required|numeric|min:0|max:99999',
            'precio_oferta' => 'nullable|numeric|min:0',
            'oferta_activa' => 'nullable|boolean',
            'unidad'        => 'required|string|max:30',
            'stock'         => 'required|integer|min:0',
            'stock_minimo'  => 'nullable|integer|min:0',
            'disponible'    => 'nullable|boolean',
            'destacado'     => 'nullable|boolean',
            'imagen'        => 'nullable|image|max:3072',
            'imagen_url'    => 'nullable|url|max:2048',
        ]);

        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $request->file('imagen')->store('solicitudes/productos', 'public');
        } elseif ($request->filled('imagen_url')) {
            $validated['imagen'] = $request->input('imagen_url');
        }
        unset($validated['imagen_url']);

        $cambiosCreados = 0;

        foreach (self::CAMPOS_PRODUCTO as $campo) {
            if (!array_key_exists($campo, $validated)) {
                continue;
            }

            $valorNuevo    = $validated[$campo];
            $valorAnterior = $producto->$campo;

            if ($valorNuevo == $valorAnterior) {
                continue;
            }

            // Reemplazar solicitud pendiente del mismo campo
            SolicitudCambio::where('tienda_id', $tienda->id)
                ->where('producto_id', $producto->id)
                ->where('campo', $campo)
                ->where('estado', 'pendiente')
                ->delete();

            SolicitudCambio::create([
                'user_id'        => $user->id,
                'tienda_id'      => $tienda->id,
                'producto_id'    => $producto->id,
                'tipo'           => 'update_producto',
                'campo'          => $campo,
                'valor_anterior' => ['value' => $valorAnterior],
                'valor_nuevo'    => ['value' => $valorNuevo],
            ]);

            $cambiosCreados++;
        }

        if ($cambiosCreados === 0) {
            return back()->with('info', 'No se detectaron cambios respecto al producto actual.');
        }

        $msg = "Solicitud enviada con {$cambiosCreados} " . ($cambiosCreados === 1 ? 'cambio' : 'cambios') . " para revisi\u00f3n del administrador.";

        return back()->with('success', $msg);
    }

    /**
     * Solicitar eliminación de un producto.
     */
    public function solicitarEliminarProducto(Producto $producto)
    {
        $user   = auth()->user();
        $tienda = $user->tiendas()->firstOrFail();

        if ($producto->tienda_id !== $tienda->id) {
            abort(403);
        }

        // No duplicar solicitudes pendientes de borrado
        $existe = SolicitudCambio::where('tienda_id', $tienda->id)
            ->where('producto_id', $producto->id)
            ->where('tipo', 'delete_producto')
            ->where('estado', 'pendiente')
            ->exists();

        if ($existe) {
            return back()->with('info', 'Ya existe una solicitud de eliminación pendiente para este producto.');
        }

        SolicitudCambio::create([
            'user_id'        => $user->id,
            'tienda_id'      => $tienda->id,
            'producto_id'    => $producto->id,
            'tipo'           => 'delete_producto',
            'campo'          => '_eliminar',
            'valor_anterior' => [
                'nombre'  => $producto->nombre,
                'precio'  => $producto->precio,
                'stock'   => $producto->stock,
                'imagen'  => $producto->imagen,
            ],
            'valor_nuevo'    => null,
        ]);

        return back()->with('success', 'Solicitud de eliminación del producto enviada al administrador.');
    }

    /**
     * Ver el estado de las solicitudes propias.
     */
    public function misSolicitudes()
    {
        $user   = auth()->user();
        $tienda = $user->tiendas()->firstOrFail();

        $solicitudes = SolicitudCambio::where('tienda_id', $tienda->id)
            ->with(['producto:id,nombre,imagen', 'revisor:id,name'])
            ->orderByRaw("FIELD(estado, 'pendiente', 'aprobado', 'rechazado')")
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($s) => [
                'id'             => $s->id,
                'tipo'           => $s->tipo,
                'campo'          => $s->campo,
                'label_campo'    => $s->labelCampo(),
                'valor_anterior' => $s->valor_anterior,
                'valor_nuevo'    => $s->valor_nuevo,
                'estado'         => $s->estado,
                'motivo_rechazo' => $s->motivo_rechazo,
                'revisor'        => $s->revisor?->name,
                'revisado_at'    => $s->revisado_at?->format('d/m/Y H:i'),
                'created_at'     => $s->created_at->format('d/m/Y H:i'),
                'producto'       => $s->producto ? ['id' => $s->producto->id, 'nombre' => $s->producto->nombre] : null,
            ]);

        return response()->json($solicitudes);
    }
}
