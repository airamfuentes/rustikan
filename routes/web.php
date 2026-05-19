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

Route::get('/buscar', [\App\Http\Controllers\BusquedaController::class, 'index'])
    ->name('buscar');

Route::get('/tienda/{tienda:slug}', function (\App\Models\Tienda $tienda) {
    $tienda->load(['categoria', 'user', 'productos' => function ($query) {
        $query->with('categoria')->where('disponible', true)->orderBy('destacado', 'desc');
    }]);

    // Cargar reseñas con datos parciales del usuario
    $resenas = $tienda->resenas()
        ->with('user:id,name,email')
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($r) {
            $nombre = $r->user?->name ?? 'Cliente';
            $email  = $r->user?->email ?? '';
            $partes = explode(' ', $nombre);

            // Mask email: ai***@gmail.com
            $emailMostrado = '';
            if ($email) {
                [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
                $visible = substr($local, 0, min(2, strlen($local)));
                $emailMostrado = $visible . str_repeat('*', max(3, strlen($local) - 2)) . '@' . $domain;
            }

            return [
                'id'          => $r->id,
                'puntuacion'  => $r->puntuacion,
                'titulo'      => $r->titulo,
                'comentario'  => $r->comentario,
                'nombre'      => $partes[0] . (count($partes) > 1 ? ' ' . strtoupper(substr($partes[1], 0, 1)) . '.' : ''),
                'email'       => $emailMostrado,
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

    // Determinar si el usuario autenticado puede reseñar.
    // Regla: 1 reseña por pedido. Mientras tenga pedidos sin reseñar en esta
    // tienda, puede crear una nueva. No exponemos al user el contador.
    $canReview    = false;
    $userReviewIds = [];
    $user = auth()->user();
    if ($user) {
        // Las reseñas existentes del user en esta tienda (para que el front
        // pueda marcarlas visualmente y mostrar botón de eliminar).
        $userReviewIds = $tienda->resenas()
            ->where('user_id', $user->id)
            ->pluck('id')
            ->all();

        if ($user->role === 'admin') {
            // Admin: siempre puede dejar/actualizar su reseña libre
            $canReview = empty($userReviewIds);
        } elseif ($user->role === 'user') {
            // Pedidos del user en esta tienda (no cancelados)
            $totalPedidos = \App\Models\Pedido::where('user_id', $user->id)
                ->whereNotIn('estado', ['cancelado'])
                ->whereHas('items', fn ($q) => $q->where('tienda_id', $tienda->id))
                ->count();

            // Reseñas del user asociadas a un pedido en esta tienda
            $totalResenadas = $tienda->resenas()
                ->where('user_id', $user->id)
                ->whereNotNull('pedido_id')
                ->count();

            $canReview = $totalPedidos > $totalResenadas;
        }
    }

    return Inertia::render('TiendaDetalle', [
        'tienda'         => $tienda,
        'resenas'        => $resenas,
        'distribucion'   => $distribucion,
        'canReview'      => $canReview,
        'userReviewIds'  => $userReviewIds,
    ]);
})->name('tienda.detalle');

Route::get('/carrito', function () {
    return Inertia::render('Carrito');
})->name('carrito');

// Chat IA (Gemini) — público, con rate limit por IP/usuario
Route::post('/api/chat-ia', [\App\Http\Controllers\ChatIAController::class, 'send'])
    ->middleware('throttle:30,1')
    ->name('chat.ia');

// Solicitud de creación de tienda (requiere autenticación)
Route::post('/vende-con-nosotros', [\App\Http\Controllers\SolicitudCreacionTiendaController::class, 'store'])
    ->middleware(['auth', 'throttle:5,10'])
    ->name('solicitud-tienda.store');

// ── Páginas informativas ────────────────────────────────────────────────────
Route::prefix('')->name('info.')->group(function () {
    Route::get('/quienes-somos',          fn() => Inertia::render('Info/QuienesSomos'))->name('quienes-somos');
    Route::get('/nuestra-mision',         fn() => Inertia::render('Info/NuestraMision'))->name('mision');
    Route::get('/contacto',               fn() => Inertia::render('Info/Contacto'))->name('contacto');
    Route::post('/contacto',              [\App\Http\Controllers\ContactoController::class, 'store'])->name('contacto.store');
    Route::get('/vende-con-nosotros',     fn() => Inertia::render('Info/VendeConNosotros'))->name('vende');
    Route::get('/trabaja-con-nosotros',   fn() => Inertia::render('Info/TrabajaConNosotros'))->name('trabaja');
    Route::post('/trabaja-con-nosotros',  [\App\Http\Controllers\TrabajaConNosotrosController::class, 'store'])
        ->middleware('throttle:5,10')
        ->name('trabaja.store');
    Route::get('/preguntas-frecuentes',   fn() => Inertia::render('Info/PreguntasFrecuentes'))->name('faq');
    Route::get('/terminos-y-condiciones', fn() => Inertia::render('Info/Terminos'))->name('terminos');
    Route::get('/politica-de-privacidad', fn() => Inertia::render('Info/Privacidad'))->name('privacidad');
    Route::get('/cookies',                fn() => Inertia::render('Info/Cookies'))->name('cookies');
});

// Stripe Checkout
Route::post('/checkout/session', [\App\Http\Controllers\StripeController::class, 'createSession'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('checkout.session');

Route::get('/checkout/success', [\App\Http\Controllers\StripeController::class, 'success'])
    ->middleware('auth')
    ->name('checkout.success');

Route::post('/stripe/webhook', [\App\Http\Controllers\StripeController::class, 'webhook'])
    ->name('stripe.webhook');

// Pedidos (usuario autenticado)
Route::middleware('auth')->group(function () {
    Route::post('/pedidos', [\App\Http\Controllers\PedidoController::class, 'store'])->name('pedidos.store');
    Route::get('/pedidos/{pedido}/confirmacion', [\App\Http\Controllers\PedidoController::class, 'show'])->name('pedidos.confirmacion');
    Route::post('/pedidos/{pedido}/cancelar', [\App\Http\Controllers\PedidoController::class, 'cancelar'])->name('pedidos.cancelar');
    Route::get('/mis-pedidos', [\App\Http\Controllers\PedidoController::class, 'index'])->name('pedidos.index');

    // Reseñas
    Route::post('/tienda/{tienda}/resenas', [\App\Http\Controllers\ResenaController::class, 'store'])->name('resenas.store');
    Route::delete('/resenas/{resena}', [\App\Http\Controllers\ResenaController::class, 'destroy'])->name('resenas.destroy');
    Route::get('/factura/{pedido}', [\App\Http\Controllers\FacturaController::class, 'show'])->name('factura.show');

    // Monedero RustiCoin
    Route::get('/monedero', [\App\Http\Controllers\RusticoinController::class, 'index'])->name('monedero.index');
    Route::post('/monedero/recargar', [\App\Http\Controllers\RusticoinController::class, 'recargar'])->name('monedero.recargar');
    Route::post('/monedero/retirar', [\App\Http\Controllers\RusticoinController::class, 'retirar'])->name('monedero.retirar');

    // Notificaciones
    Route::get('/notificaciones', [\App\Http\Controllers\NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/{notificacion}/leer', [\App\Http\Controllers\NotificacionController::class, 'marcarLeida'])->name('notificaciones.leer');
    Route::post('/notificaciones/leer-todas', [\App\Http\Controllers\NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.leer-todas');

    // Tiendas favoritas
    Route::get('/mis-favoritas', [\App\Http\Controllers\FavoritoController::class, 'index'])->name('favoritos.index');
    Route::post('/favoritos/{tienda}/toggle', [\App\Http\Controllers\FavoritoController::class, 'toggle'])->name('favoritos.toggle');
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
        if ($user->isSupplier()) {
            return redirect()->route('supplier.dashboard');
        }
        return redirect()->route('home');
    })->name('dashboard');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Owner Routes
Route::middleware(['auth', 'owner'])->prefix('mi-tienda')->name('owner.')->group(function () {
    Route::get('/panel', [\App\Http\Controllers\Owner\PanelController::class, 'index'])->name('panel');
    Route::get('/pedidos/{pedido}', [\App\Http\Controllers\Owner\PanelController::class, 'pedidoDetalle'])->name('pedido.show');
    Route::get('/editar', [\App\Http\Controllers\Owner\TiendaController::class, 'edit'])->name('tienda.edit');
    // Productos (solo lectura para editar — cambios van por solicitud)
    Route::get('/productos/{producto}/editar', [\App\Http\Controllers\Owner\ProductoController::class, 'edit'])->name('producto.edit');
    Route::post('/productos/{producto}/oferta', [\App\Http\Controllers\Owner\ProductoController::class, 'solicitarOferta'])->name('producto.oferta');
    // Solicitudes de cambio
    Route::post('/solicitar/tienda',                    [\App\Http\Controllers\Owner\SolicitudController::class, 'solicitarCambioTienda'])->name('solicitar.tienda');
    Route::post('/solicitar/productos',                 [\App\Http\Controllers\Owner\SolicitudController::class, 'solicitarCrearProducto'])->name('solicitar.producto.crear');
    Route::post('/solicitar/productos/{producto}',      [\App\Http\Controllers\Owner\SolicitudController::class, 'solicitarEditarProducto'])->name('solicitar.producto.editar');
    Route::delete('/solicitar/productos/{producto}',    [\App\Http\Controllers\Owner\SolicitudController::class, 'solicitarEliminarProducto'])->name('solicitar.producto.eliminar');
    Route::get('/mis-solicitudes',                      [\App\Http\Controllers\Owner\SolicitudController::class, 'misSolicitudes'])->name('mis.solicitudes');
    // Exportaciones (PDF imprimible)
    Route::get('/exportar/beneficios',  [\App\Http\Controllers\Owner\ExportController::class, 'beneficiosPdf'])->name('exportar.beneficios');
    Route::get('/exportar/pedidos',     [\App\Http\Controllers\Owner\ExportController::class, 'pedidosPdf'])->name('exportar.pedidos');
});

// Admin Routes
Route::middleware(['auth', 'admin', 'throttle:60,1'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Tiendas
    Route::resource('tiendas', \App\Http\Controllers\Admin\TiendaController::class);
    Route::post('/tiendas/{tienda}/toggle-visible', [\App\Http\Controllers\Admin\TiendaController::class, 'toggleVisible'])->name('tiendas.toggle-visible');
    Route::post('/tiendas/{tienda}/toggle-active', [\App\Http\Controllers\Admin\TiendaController::class, 'toggleActive'])->name('tiendas.toggle-active');

    // Reseñas de una tienda (listado + moderación)
    Route::get('/tiendas/{tienda}/resenas', [\App\Http\Controllers\Admin\TiendaController::class, 'resenas'])->name('tiendas.resenas');
    Route::delete('/tiendas/{tienda}/resenas/{resena}', [\App\Http\Controllers\Admin\TiendaController::class, 'destroyResena'])->name('tiendas.resenas.destroy');
    
    // Productos (anidados bajo tiendas)
    Route::get('/tiendas/{tienda}/productos', [\App\Http\Controllers\Admin\ProductoController::class, 'tiendaProductos'])->name('tiendas.productos.index');
    Route::get('/tiendas/{tienda}/productos/crear', [\App\Http\Controllers\Admin\ProductoController::class, 'tiendaCrear'])->name('tiendas.productos.create');

    // Productos resource (edit/update/destroy/store reutilizados)
    Route::resource('productos', \App\Http\Controllers\Admin\ProductoController::class);
    Route::post('/productos/{producto}/update-stock', [\App\Http\Controllers\Admin\ProductoController::class, 'updateStock'])->name('productos.update-stock');
    Route::post('/productos/{producto}/toggle-oferta', [\App\Http\Controllers\Admin\ProductoController::class, 'toggleOferta'])->name('productos.toggle-oferta');
    
    // Pedidos
    Route::resource('pedidos', \App\Http\Controllers\Admin\PedidoController::class);
    Route::post('/pedidos/{pedido}/cancelar', [\App\Http\Controllers\Admin\PedidoController::class, 'cancelar'])->name('pedidos.cancelar');
    
    // Usuarios
    Route::resource('usuarios', \App\Http\Controllers\Admin\UsuarioController::class);
    
    // Ingresos
    Route::get('/ingresos', [\App\Http\Controllers\Admin\IngresoController::class, 'index'])->name('ingresos.index');
    // Exportaciones admin (PDF imprimible)
    Route::get('/exportar/ingresos',         [\App\Http\Controllers\Admin\ExportController::class, 'ingresosPdf'])->name('exportar.ingresos');
    Route::get('/exportar/ingresos-tiendas', [\App\Http\Controllers\Admin\ExportController::class, 'ingresosPorTiendaPdf'])->name('exportar.ingresos-tiendas');
    Route::get('/exportar/pedidos',          [\App\Http\Controllers\Admin\ExportController::class, 'pedidosPdf'])->name('exportar.pedidos');
    
    // Categorías
    Route::resource('categorias', \App\Http\Controllers\Admin\CategoriaController::class);

    // Solicitudes de cambio de tiendas
    Route::get('/solicitudes', [\App\Http\Controllers\Admin\SolicitudController::class, 'index'])->name('solicitudes.index');
    Route::post('/solicitudes/{solicitud}/aprobar', [\App\Http\Controllers\Admin\SolicitudController::class, 'aprobar'])->name('solicitudes.aprobar');
    Route::post('/solicitudes/{solicitud}/rechazar', [\App\Http\Controllers\Admin\SolicitudController::class, 'rechazar'])->name('solicitudes.rechazar');
    Route::post('/solicitudes/tienda/{tienda}/aprobar-todas', [\App\Http\Controllers\Admin\SolicitudController::class, 'aprobarTodas'])->name('solicitudes.aprobar-todas');
    Route::post('/solicitudes/tienda/{tienda}/rechazar-todas', [\App\Http\Controllers\Admin\SolicitudController::class, 'rechazarTodas'])->name('solicitudes.rechazar-todas');

    // Solicitudes de creación de nueva tienda
    Route::get('/solicitudes-creacion', [\App\Http\Controllers\Admin\SolicitudCreacionTiendaController::class, 'index'])->name('solicitudes-creacion.index');
    Route::post('/solicitudes-creacion/{solicitud}/aprobar', [\App\Http\Controllers\Admin\SolicitudCreacionTiendaController::class, 'aprobar'])->name('solicitudes-creacion.aprobar');
    Route::post('/solicitudes-creacion/{solicitud}/rechazar', [\App\Http\Controllers\Admin\SolicitudCreacionTiendaController::class, 'rechazar'])->name('solicitudes-creacion.rechazar');

    // Solicitudes de empleo (formulario "Trabaja con nosotros")
    Route::get('/solicitudes-empleo',                          [\App\Http\Controllers\Admin\SolicitudEmpleoController::class, 'index'])->name('solicitudes-empleo.index');
    Route::patch('/solicitudes-empleo/{solicitud}/estado',     [\App\Http\Controllers\Admin\SolicitudEmpleoController::class, 'updateEstado'])->name('solicitudes-empleo.estado');
    Route::get('/solicitudes-empleo/{solicitud}/cv',           [\App\Http\Controllers\Admin\SolicitudEmpleoController::class, 'downloadCv'])->name('solicitudes-empleo.cv');
    Route::delete('/solicitudes-empleo/{solicitud}',           [\App\Http\Controllers\Admin\SolicitudEmpleoController::class, 'destroy'])->name('solicitudes-empleo.destroy');
});

// ── Panel Supplier (almacén) ──────────────────────────────────────────────────
Route::prefix('supplier')->name('supplier.')->middleware(['auth', 'supplier'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Supplier\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pedidos', [\App\Http\Controllers\Supplier\PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/{pedido}', [\App\Http\Controllers\Supplier\PedidoController::class, 'show'])->name('pedidos.show');
    Route::post('/pedidos/{pedido}/estado', [\App\Http\Controllers\Supplier\PedidoController::class, 'cambiarEstado'])->name('pedidos.estado');
    Route::get('/historial', [\App\Http\Controllers\Supplier\PedidoController::class, 'historial'])->name('historial');
    Route::get('/stock', [\App\Http\Controllers\Supplier\StockController::class, 'index'])->name('stock');
    Route::get('/stock/{tienda}', [\App\Http\Controllers\Supplier\StockController::class, 'tienda'])->name('stock.tienda');

    // Exportaciones supplier (PDF imprimible)
    Route::get('/exportar/pedidos',           [\App\Http\Controllers\Supplier\ExportController::class, 'pedidosPdf'])->name('exportar.pedidos');
    Route::get('/exportar/historial',         [\App\Http\Controllers\Supplier\ExportController::class, 'historialPdf'])->name('exportar.historial');
    Route::get('/exportar/pedidos/{pedido}',  [\App\Http\Controllers\Supplier\ExportController::class, 'pedidoPdf'])->name('exportar.pedido');
});

// ── Chat Admin-Supplier (JSON API) ────────────────────────────────────────────
Route::middleware(['auth'])->prefix('api/chat-almacen')->name('chat.almacen.')->group(function () {
    Route::get('/mensajes', [\App\Http\Controllers\ChatAdminSupplierController::class, 'getMensajes'])->name('mensajes');
    Route::post('/enviar', [\App\Http\Controllers\ChatAdminSupplierController::class, 'enviar'])->middleware('throttle:60,1')->name('enviar');
    Route::get('/conversaciones', [\App\Http\Controllers\ChatAdminSupplierController::class, 'conversaciones'])->name('conversaciones');
    Route::get('/no-leidos', [\App\Http\Controllers\ChatAdminSupplierController::class, 'noLeidos'])->name('no-leidos');
});

require __DIR__.'/auth.php';
