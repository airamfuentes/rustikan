<?php

namespace App\Http\Controllers;

use App\Mail\PedidoConfirmado;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\Resena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class PedidoController extends Controller
{
    /**
     * Crear un nuevo pedido desde el carrito del usuario.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items'                    => 'required|array|min:1',
            'items.*.id'               => 'required|integer|exists:productos,id',
            'items.*.cantidad'         => 'required|integer|min:1|max:99',
            'items.*.precio'           => 'required|numeric|min:0',
            'items.*.nombre'           => 'required|string|max:255',
            'items.*.tienda_id'        => 'required|integer|exists:tiendas,id',
            'items.*.tienda_nombre'    => 'required|string|max:255',
            'items.*.imagen'           => 'nullable|string|max:500',
            'items.*.unidad'           => 'nullable|string|max:50',
            'direccion_envio'          => 'required|string|max:500',
            'telefono_contacto'        => 'required|string|max:20',
            'notas'                    => 'nullable|string|max:1000',
        ]);

        try {
            $pedido = DB::transaction(function () use ($validated) {
                // Verificar stock para cada producto (con bloqueo optimista)
                foreach ($validated['items'] as $item) {
                    $producto = Producto::lockForUpdate()->find($item['id']);

                    if (!$producto || !$producto->disponible) {
                        throw new \Exception("El producto \"{$item['nombre']}\" ya no está disponible.");
                    }

                    if ($producto->stock < $item['cantidad']) {
                        throw new \Exception("Stock insuficiente para \"{$item['nombre']}\". Quedan {$producto->stock} unidades.");
                    }
                }

                // Calcular totales usando los precios reales de la BD (no del cliente)
                $subtotal = 0;
                $itemsConPrecio = [];

                foreach ($validated['items'] as $item) {
                    $producto = Producto::find($item['id']);
                    $precioReal = $producto->precio_oferta ?? $producto->precio;
                    $subtotal += $precioReal * $item['cantidad'];

                    $itemsConPrecio[] = array_merge($item, ['precio_real' => (float) $precioReal]);
                }

                $gastosEnvio = $subtotal >= 50 ? 0.00 : 2.50;
                $total       = $subtotal + $gastosEnvio;

                // Crear el pedido
                $pedido = Pedido::create([
                    'user_id'           => auth()->id(),
                    'numero_pedido'     => Pedido::generateOrderNumber(),
                    'estado'            => 'pendiente',
                    'subtotal'          => $subtotal,
                    'gastos_envio'      => $gastosEnvio,
                    'total'             => $total,
                    'direccion_envio'   => $validated['direccion_envio'],
                    'telefono_contacto' => $validated['telefono_contacto'],
                    'notas'             => $validated['notas'] ?? null,
                ]);

                // Crear items y decrementar stock
                foreach ($itemsConPrecio as $item) {
                    $pedido->items()->create([
                        'producto_id'    => $item['id'],
                        'tienda_id'      => $item['tienda_id'],
                        'producto_nombre'=> $item['nombre'],
                        'tienda_nombre'  => $item['tienda_nombre'],
                        'producto_imagen'=> $item['imagen'] ?? null,
                        'cantidad'       => $item['cantidad'],
                        'precio_unitario'=> $item['precio_real'],
                        'subtotal'       => $item['precio_real'] * $item['cantidad'],
                    ]);

                    Producto::find($item['id'])->decrement('stock', $item['cantidad']);
                }

                return $pedido;
            });

            // Enviar email de confirmación al comprador
            $pedido->load(['items', 'user']);
            Mail::to($pedido->user->email)->send(new PedidoConfirmado($pedido));

            return redirect()
                ->route('pedidos.confirmacion', $pedido)
                ->with('success', '¡Pedido realizado con éxito!');

        } catch (\Exception $e) {
            return back()->withErrors(['stock' => $e->getMessage()]);
        }
    }

    /**
     * Mostrar página de confirmación de un pedido.
     */
    public function show(Pedido $pedido)
    {
        abort_unless($pedido->user_id === auth()->id(), 403);

        $pedido->load(['items.producto', 'items.tienda']);

        return Inertia::render('PedidoConfirmacion', [
            'pedido' => $pedido,
        ]);
    }

    /**
     * Listado de pedidos del usuario autenticado.
     */
    public function index()
    {
        $userId = auth()->id();

        $pedidosActivos = Pedido::where('user_id', $userId)
            ->whereIn('estado', ['pendiente', 'confirmado', 'preparando', 'en_camino'])
            ->with(['items.tienda:id,nombre,slug'])
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->get();

        $pedidosHistorial = Pedido::where('user_id', $userId)
            ->whereIn('estado', ['entregado', 'cancelado'])
            ->with(['items.tienda:id,nombre,slug'])
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $tiendaIdsDelivered = PedidoItem::whereHas('pedido', fn($q) =>
            $q->where('user_id', $userId)->where('estado', 'entregado')
        )->pluck('tienda_id')->unique();

        $yaReseniados = Resena::where('user_id', $userId)
            ->whereIn('tienda_id', $tiendaIdsDelivered)
            ->pluck('tienda_id');

        $reviewableStoreIds = $tiendaIdsDelivered->diff($yaReseniados)->values();

        return Inertia::render('MisPedidos', [
            'pedidosActivos'    => $pedidosActivos,
            'pedidosHistorial'  => $pedidosHistorial,
            'reviewableStoreIds'=> $reviewableStoreIds,
        ]);
    }
}
