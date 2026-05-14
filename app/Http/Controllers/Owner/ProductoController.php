<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Notificacion;
use App\Models\Producto;
use App\Models\SolicitudCambio;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * El owner NO aplica cambios directos sobre productos. Esta clase sirve la
 * vista del formulario de edición y gestiona el flujo de OFERTAS, que también
 * pasa por aprobación del administrador.
 */
class ProductoController extends Controller
{
    public function edit(Producto $producto)
    {
        $tienda = auth()->user()->tiendas()->firstOrFail();

        if ($producto->tienda_id !== $tienda->id) {
            abort(403);
        }

        $categorias = Categoria::all(['id', 'nombre', 'icono']);

        return Inertia::render('Owner/Producto/Editar', [
            'producto'   => $producto->load('categoria'),
            'categorias' => $categorias,
            'tienda'     => $tienda,
        ]);
    }

    /**
     * Solicitud combinada para activar / desactivar la oferta de un producto.
     *
     * - Al ACTIVAR la oferta: el owner debe indicar el `precio_oferta` nuevo;
     *   la solicitud guarda tanto el flag (oferta_activa=true) como el precio
     *   de oferta propuesto, junto con el precio actual del producto como
     *   referencia para el admin.
     * - Al DESACTIVAR la oferta: solo el flag cambia a false.
     *
     * En cualquier caso el cambio NO se aplica hasta que el admin lo apruebe.
     */
    public function solicitarOferta(Request $request, Producto $producto)
    {
        $tienda = auth()->user()->tiendas()->firstOrFail();

        if ($producto->tienda_id !== $tienda->id) {
            abort(403);
        }

        $activar = (bool) $request->input('activar', !$producto->oferta_activa);

        // Si se activa: el precio de oferta es obligatorio y debe ser menor
        // que el precio actual del producto.
        if ($activar) {
            $data = $request->validate([
                'precio_oferta' => ['required', 'numeric', 'min:0.01', 'lt:' . $producto->precio],
            ], [
                'precio_oferta.required' => 'Indica el precio de la oferta.',
                'precio_oferta.lt'       => 'El precio de oferta debe ser menor que el precio actual del producto (' . number_format($producto->precio, 2, ',', '.') . ' €).',
            ]);
            $precioOferta = round((float) $data['precio_oferta'], 2);
        } else {
            // Al desactivar, no necesitamos precio.
            $precioOferta = null;
        }

        // Evitar duplicados de solicitudes pendientes de oferta para el mismo producto.
        SolicitudCambio::where('tienda_id', $tienda->id)
            ->where('producto_id', $producto->id)
            ->where('campo', '_oferta')
            ->where('estado', 'pendiente')
            ->delete();

        SolicitudCambio::create([
            'user_id'        => auth()->id(),
            'tienda_id'      => $tienda->id,
            'producto_id'    => $producto->id,
            'tipo'           => 'update_producto',
            'campo'          => '_oferta',
            'valor_anterior' => [
                'oferta_activa' => (bool) $producto->oferta_activa,
                'precio'        => (float) $producto->precio,
                'precio_oferta' => $producto->precio_oferta !== null ? (float) $producto->precio_oferta : null,
            ],
            'valor_nuevo'    => [
                'oferta_activa' => $activar,
                'precio_oferta' => $precioOferta,
            ],
        ]);

        // Notificar a los admins
        Notificacion::enviarAdmins(
            'nueva_solicitud_producto',
            'Solicitud de oferta en producto',
            "La tienda «{$tienda->nombre}» ha solicitado " . ($activar ? "activar una oferta a {$precioOferta} €" : 'desactivar la oferta') . " para «{$producto->nombre}».",
            route('admin.solicitudes.index'),
            'tag',
            'orange'
        );

        return back()->with('success', 'Solicitud de oferta enviada al administrador para su revisión.');
    }
}
