<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('/tienda/{id}', function ($id) {
    // Datos de ejemplo de tiendas de Lanzarote
    $tiendas = [
        1 => [
            'id' => 1,
            'nombre' => 'Mi Tienda Lanzarote',
            'ubicacion' => 'Arrecife, Lanzarote',
            'descripcion' => 'Productos locales frescos de toda la isla.',
            'imagen' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&h=600&fit=crop',
            'valoracion' => 5.0,
            'ventas' => 0,
            'km' => 0,
        ],
        2 => [
            'id' => 2,
            'nombre' => 'Finca La Geria',
            'ubicacion' => 'La Geria, Lanzarote',
            'descripcion' => 'Productos volcánicos de La Geria.',
            'imagen' => 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=800&h=600&fit=crop',
            'valoracion' => 4.8,
            'ventas' => 156,
            'km' => 0,
        ],
        3 => [
            'id' => 3,
            'nombre' => 'Mariscos de Arrieta',
            'ubicacion' => 'Arrieta, Lanzarote',
            'descripcion' => 'Pescado fresco y marisco del puerto.',
            'imagen' => 'https://images.unsplash.com/photo-1559737558-2f5a419b1e85?w=800&h=600&fit=crop',
            'valoracion' => 4.9,
            'ventas' => 89,
            'km' => 0,
        ],
        4 => [
            'id' => 4,
            'nombre' => 'Quesería Haría',
            'ubicacion' => 'Haría, Lanzarote',
            'descripcion' => 'Quesos artesanales de cabra majorera.',
            'imagen' => 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=800&h=600&fit=crop',
            'valoracion' => 4.7,
            'ventas' => 203,
            'km' => 0,
        ],
        5 => [
            'id' => 5,
            'nombre' => 'Horno de Teguise',
            'ubicacion' => 'Teguise, Lanzarote',
            'descripcion' => 'Pan artesano y repostería tradicional.',
            'imagen' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=800&h=600&fit=crop',
            'valoracion' => 4.6,
            'ventas' => 312,
            'km' => 0,
        ],
    ];
    
    $tienda = $tiendas[$id] ?? abort(404);
    
    return Inertia::render('TiendaDetalle', [
        'tienda' => $tienda
    ]);
})->name('tienda.detalle');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
