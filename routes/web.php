<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoriaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $tiendas = \App\Models\Tienda::with(['categoria', 'user'])
        ->where('visible', true)
        ->where('activa', true)
        ->get();

    $categorias = \App\Models\Categoria::withCount(['tiendas' => function ($q) {
        $q->where('visible', true)->where('activa', true);
    }])->get();

    return Inertia::render('Inicio', [
        'tiendas'    => $tiendas,
        'categorias' => $categorias,
    ]);
})->name('home');

Route::get('/categoria/{categoria:slug}', [CategoriaController::class, 'show'])
    ->name('categoria.tiendas');

Route::get('/tienda/{tienda:slug}', function (\App\Models\Tienda $tienda) {
    $tienda->load(['categoria', 'user', 'productos' => function ($query) {
        $query->with('categoria')->where('disponible', true)->orderBy('destacado', 'desc');
    }]);

    // Cargar reseñas con datos parciales del usuario
    $resenas = $tienda->resenas()
        ->with('user:id,name')
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($r) {
            $nombre = $r->user?->name ?? 'Cliente';
            // Privacy: show only first name + first letter of last name
            $partes = explode(' ', $nombre);
            $nombreMostrado = $partes[0] . (count($partes) > 1 ? ' ' . strtoupper(substr($partes[1], 0, 1)) . '.' : '');
            return [
                'id'          => $r->id,
                'puntuacion'  => $r->puntuacion,
                'titulo'      => $r->titulo,
                'comentario'  => $r->comentario,
                'nombre'      => $nombreMostrado,
                'inicial'     => strtoupper(substr($partes[0], 0, 1)),
                'user_id'     => $r->user_id,
                'created_at'  => $r->created_at->diffForHumans(),
            ];
        });

    // Distribución de estrellas
    $distribucion = [];
    for ($i = 5; $i >= 1; $i--) {
        $count = $tienda->resenas()->where('puntuacion', $i)->count();
        $distribucion[$i] = $count;
    }

    // Determinar si el usuario autenticado puede reseñar
    $canReview  = false;
    $userReview = null;
    $user = auth()->user();
    if ($user && $user->role === 'user') {
        $userReview = $tienda->resenas()->where('user_id', $user->id)->first();
        if (!$userReview) {
            $canReview = \App\Models\PedidoItem::where('tienda_id', $tienda->id)
                ->whereHas('pedido', fn($q) => $q->where('user_id', $user->id)
                    ->whereIn('estado', ['entregado', 'completado']))
                ->exists();
        }
    }

    return Inertia::render('TiendaDetalle', [
        'tienda'       => $tienda,
        'resenas'      => $resenas,
        'distribucion' => $distribucion,
        'canReview'    => $canReview,
        'userReview'   => $userReview ? [
            'id'         => $userReview->id,
            'puntuacion' => $userReview->puntuacion,
            'titulo'     => $userReview->titulo,
            'comentario' => $userReview->comentario,
        ] : null,
    ]);
})->name('tienda.detalle');

Route::get('/carrito', function () {
    return Inertia::render('Carrito');
})->name('carrito');

// Pedidos (usuario autenticado)
Route::middleware('auth')->group(function () {
    Route::post('/pedidos', [\App\Http\Controllers\PedidoController::class, 'store'])->name('pedidos.store');
    Route::get('/pedidos/{pedido}/confirmacion', [\App\Http\Controllers\PedidoController::class, 'show'])->name('pedidos.confirmacion');
    Route::get('/mis-pedidos', [\App\Http\Controllers\PedidoController::class, 'index'])->name('pedidos.index');

    // Reseñas
    Route::post('/tienda/{tienda}/resenas', [\App\Http\Controllers\ResenaController::class, 'store'])->name('resenas.store');
    Route::delete('/resenas/{resena}', [\App\Http\Controllers\ResenaController::class, 'destroy'])->name('resenas.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->isOwner()) {
            return redirect()->route('owner.panel');
        }
        return redirect()->route('profile.edit');
    })->name('dashboard');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Owner Routes
Route::middleware(['auth', 'owner'])->prefix('mi-tienda')->name('owner.')->group(function () {
    Route::get('/panel', [\App\Http\Controllers\Owner\PanelController::class, 'index'])->name('panel');
    Route::get('/editar', [\App\Http\Controllers\Owner\TiendaController::class, 'edit'])->name('tienda.edit');
    Route::post('/editar', [\App\Http\Controllers\Owner\TiendaController::class, 'update'])->name('tienda.update');
    // Productos
    Route::get('/productos/{producto}/editar', [\App\Http\Controllers\Owner\ProductoController::class, 'edit'])->name('producto.edit');
    Route::post('/productos/{producto}', [\App\Http\Controllers\Owner\ProductoController::class, 'update'])->name('producto.update');
});

// Admin Routes
Route::middleware(['auth', 'admin', 'throttle:60,1'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Tiendas
    Route::resource('tiendas', \App\Http\Controllers\Admin\TiendaController::class);
    Route::post('/tiendas/{tienda}/toggle-visible', [\App\Http\Controllers\Admin\TiendaController::class, 'toggleVisible'])->name('tiendas.toggle-visible');
    Route::post('/tiendas/{tienda}/toggle-active', [\App\Http\Controllers\Admin\TiendaController::class, 'toggleActive'])->name('tiendas.toggle-active');
    
    // Productos (anidados bajo tiendas)
    Route::get('/tiendas/{tienda}/productos', [\App\Http\Controllers\Admin\ProductoController::class, 'tiendaProductos'])->name('tiendas.productos.index');
    Route::get('/tiendas/{tienda}/productos/crear', [\App\Http\Controllers\Admin\ProductoController::class, 'tiendaCrear'])->name('tiendas.productos.create');

    // Productos resource (edit/update/destroy/store reutilizados)
    Route::resource('productos', \App\Http\Controllers\Admin\ProductoController::class);
    Route::post('/productos/{producto}/update-stock', [\App\Http\Controllers\Admin\ProductoController::class, 'updateStock'])->name('productos.update-stock');
    
    // Pedidos
    Route::resource('pedidos', \App\Http\Controllers\Admin\PedidoController::class);
    
    // Usuarios
    Route::resource('usuarios', \App\Http\Controllers\Admin\UsuarioController::class);
    
    // Ingresos
    Route::get('/ingresos', [\App\Http\Controllers\Admin\IngresoController::class, 'index'])->name('ingresos.index');
    
    // Categorías
    Route::resource('categorias', \App\Http\Controllers\Admin\CategoriaController::class);
});

require __DIR__.'/auth.php';
