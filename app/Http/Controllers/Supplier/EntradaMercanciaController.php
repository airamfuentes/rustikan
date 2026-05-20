<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\EntradaMercancia;
use App\Models\Producto;
use App\Models\Tienda;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EntradaMercanciaController extends Controller
{
    public function index(Request $request)
    {
        $query = EntradaMercancia::with([
            'producto:id,nombre,unidad,imagen',
            'tienda:id,nombre,logo',
            'usuario:id,name',
        ])->latest();

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->whereHas('producto', fn($p) => $p->where('nombre', 'like', "%{$term}%"))
                  ->orWhereHas('tienda', fn($t) => $t->where('nombre', 'like', "%{$term}%"))
                  ->orWhere('numero_documento', 'like', "%{$term}%")
                  ->orWhere('proveedor', 'like', "%{$term}%");
            });
        }

        if ($request->filled('tienda_id')) {
            $query->where('tienda_id', $request->tienda_id);
        }

        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        $entradas = $query->paginate(20)->withQueryString();

        $tiendas = Tienda::select('id', 'nombre')->where('activa', true)->orderBy('nombre')->get();

        $stats = [
            'total_entradas'   => EntradaMercancia::count(),
            'hoy'              => EntradaMercancia::whereDate('created_at', today())->count(),
            'total_unidades'   => (int) EntradaMercancia::sum('cantidad_entrada'),
            'proveedores'      => EntradaMercancia::whereNotNull('proveedor')->distinct('proveedor')->count('proveedor'),
        ];

        return Inertia::render('Supplier/EntradaMercancia/Index', [
            'entradas' => $entradas,
            'tiendas'  => $tiendas,
            'stats'    => $stats,
            'filters'  => $request->only(['search', 'tienda_id', 'desde', 'hasta']),
        ]);
    }

    public function create(Request $request)
    {
        $tiendas = Tienda::select('id', 'nombre')->where('activa', true)->orderBy('nombre')->get();

        $tiendaId = $request->tienda_id ?? $tiendas->first()?->id;

        $productos = $tiendaId
            ? Producto::select('id', 'nombre', 'stock', 'stock_minimo', 'unidad', 'imagen', 'tienda_id')
                ->where('tienda_id', $tiendaId)
                ->orderBy('nombre')
                ->get()
            : collect();

        return Inertia::render('Supplier/EntradaMercancia/Create', [
            'tiendas'   => $tiendas,
            'productos' => $productos,
            'filters'   => ['tienda_id' => $tiendaId],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'producto_id'      => ['required', 'exists:productos,id'],
            'cantidad_entrada'  => ['required', 'integer', 'min:1'],
            'numero_documento'  => ['nullable', 'string', 'max:255'],
            'proveedor'         => ['nullable', 'string', 'max:255'],
            'notas'             => ['nullable', 'string', 'max:1000'],
        ]);

        $producto = Producto::findOrFail($data['producto_id']);

        $stockAnterior = $producto->stock;
        $stockNuevo    = $stockAnterior + $data['cantidad_entrada'];

        // Update stock — triggers ProductoObserver for email alerts
        $producto->update(['stock' => $stockNuevo]);

        EntradaMercancia::create([
            'producto_id'      => $producto->id,
            'tienda_id'        => $producto->tienda_id,
            'usuario_id'       => auth()->id(),
            'stock_anterior'   => $stockAnterior,
            'cantidad_entrada' => $data['cantidad_entrada'],
            'stock_nuevo'      => $stockNuevo,
            'numero_documento' => $data['numero_documento'] ?? null,
            'proveedor'        => $data['proveedor'] ?? null,
            'notas'            => $data['notas'] ?? null,
        ]);

        return redirect()->route('supplier.entradas.index')
            ->with('success', "Entrada registrada: +{$data['cantidad_entrada']} {$producto->unidad} de {$producto->nombre}");
    }

    public function productosDetienda(Request $request, Tienda $tienda)
    {
        $productos = Producto::select('id', 'nombre', 'stock', 'stock_minimo', 'unidad', 'imagen', 'tienda_id')
            ->where('tienda_id', $tienda->id)
            ->orderBy('nombre')
            ->get();

        return response()->json($productos);
    }
}
