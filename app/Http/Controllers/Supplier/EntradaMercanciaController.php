<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\EntradaMercancia;
use App\Models\Producto;
use App\Models\Tienda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class EntradaMercanciaController extends Controller
{
    private function emptyResponse(Request $request): \Inertia\Response
    {
        return Inertia::render('Supplier/EntradaMercancia/Index', [
            'entradas'          => ['data' => [], 'last_page' => 1, 'total' => 0, 'from' => null, 'to' => null, 'prev_page_url' => null, 'next_page_url' => null],
            'tiendas'           => [],
            'stats'             => ['total_entradas' => 0, 'hoy' => 0, 'total_unidades' => 0, 'proveedores' => 0],
            'filters'           => [],
            '_migrationPending' => true,
        ]);
    }

    public function index(Request $request)
    {
        if (!Schema::hasTable('entradas_mercancia')) {
            return $this->emptyResponse($request);
        }

        try {

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

        } catch (\Throwable) {
            return $this->emptyResponse($request);
        }
    }

    public function create(Request $request)
    {
        $tiendas = Tienda::select('id', 'nombre')->where('activa', true)->orderBy('nombre')->get();

        // Next albaran number: global counter across all tiendas
        $siguiente = 1;
        if (Schema::hasTable('entradas_mercancia')) {
            try {
                $siguiente = EntradaMercancia::max('id') + 1;
                if ($siguiente < 1) $siguiente = 1;
            } catch (\Throwable) {}
        }
        $nextAlbaran = 'ALB-' . date('Y') . '-' . str_pad($siguiente, 4, '0', STR_PAD_LEFT);

        return Inertia::render('Supplier/EntradaMercancia/Create', [
            'tiendas'     => $tiendas,
            'nextAlbaran' => $nextAlbaran,
        ]);
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('entradas_mercancia')) {
            return back()->with('error', 'La tabla de entradas no está disponible aún. Ejecuta las migraciones pendientes.');
        }

        $data = $request->validate([
            'tienda_id'              => ['required', 'exists:tiendas,id'],
            'productos'              => ['required', 'array', 'min:1'],
            'productos.*.producto_id'=> ['required', 'exists:productos,id'],
            'productos.*.cantidad'   => ['required', 'integer', 'min:1'],
            'numero_documento'       => ['required', 'string', 'max:255'],
            'proveedor'              => ['nullable', 'string', 'max:255'],
            'notas'                  => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $totalUnidades = 0;
            $nombresProds  = [];

            foreach ($data['productos'] as $item) {
                $producto = Producto::findOrFail($item['producto_id']);

                $stockAnterior = $producto->stock;
                $stockNuevo    = $stockAnterior + $item['cantidad'];

                // Update stock — triggers ProductoObserver for email alerts
                $producto->update(['stock' => $stockNuevo]);

                EntradaMercancia::create([
                    'producto_id'      => $producto->id,
                    'tienda_id'        => $data['tienda_id'],
                    'usuario_id'       => auth()->id(),
                    'stock_anterior'   => $stockAnterior,
                    'cantidad_entrada' => $item['cantidad'],
                    'stock_nuevo'      => $stockNuevo,
                    'numero_documento' => $data['numero_documento'],
                    'proveedor'        => $data['proveedor'] ?? null,
                    'notas'            => $data['notas'] ?? null,
                ]);

                $totalUnidades += $item['cantidad'];
                $nombresProds[] = $producto->nombre;
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al guardar la entrada: ' . $e->getMessage());
        }

        $resumen = count($nombresProds) === 1
            ? "+{$totalUnidades} uds. de {$nombresProds[0]}"
            : count($nombresProds) . " productos, {$totalUnidades} unidades totales";

        return redirect()->route('supplier.entradas.index')
            ->with('success', "Entrada registrada — albarán {$data['numero_documento']}: {$resumen}");
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
