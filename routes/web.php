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
        $query->where('disponible', true)->orderBy('destacado', 'desc');
    }]);

    return Inertia::render('TiendaDetalle', [
        'tienda' => $tienda,
    ]);
})->name('tienda.detalle');

Route::get('/carrito', function () {
    return Inertia::render('Carrito');
})->name('carrito');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
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
