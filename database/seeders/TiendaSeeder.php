<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tienda;

class TiendaSeeder extends Seeder
{
    public function run(): void
    {
        // ── Usuarios propietarios ────────────────────────────────────────────
        $juan   = User::firstOrCreate(['email' => 'juan@example.com'], [
            'name' => 'Juan García', 'password' => bcrypt('password'),
            'role' => 'user', 'telefono' => '928234567', 'direccion' => 'Teguise, Lanzarote',
        ]);
        $maria  = User::firstOrCreate(['email' => 'maria@example.com'], [
            'name' => 'María López', 'password' => bcrypt('password'),
            'role' => 'user', 'telefono' => '928345678', 'direccion' => 'Puerto del Carmen, Lanzarote',
        ]);

        $pedro  = User::firstOrCreate(['email' => 'pedro@example.com'], [
            'name' => 'Pedro Cabrera', 'password' => bcrypt('password'),
            'role' => 'user', 'telefono' => '928456789', 'direccion' => 'Arrecife, Lanzarote',
        ]);
        $ana    = User::firstOrCreate(['email' => 'ana@example.com'], [
            'name' => 'Ana Martín', 'password' => bcrypt('password'),
            'role' => 'user', 'telefono' => '928567890', 'direccion' => 'Yaiza, Lanzarote',
        ]);
        $carlos = User::firstOrCreate(['email' => 'carlos@example.com'], [
            'name' => 'Carlos Díaz', 'password' => bcrypt('password'),
            'role' => 'user', 'telefono' => '928678901', 'direccion' => 'San Bartolomé, Lanzarote',
        ]);
        $sofia  = User::firstOrCreate(['email' => 'sofia@example.com'], [
            'name' => 'Sofía Herrera', 'password' => bcrypt('password'),
            'role' => 'user', 'telefono' => '928789012', 'direccion' => 'Puerto del Carmen, Lanzarote',
        ]);
        $luis   = User::firstOrCreate(['email' => 'luis@example.com'], [
            'name' => 'Luis Flores', 'password' => bcrypt('password'),
            'role' => 'user', 'telefono' => '928890123', 'direccion' => 'Costa Teguise, Lanzarote',
        ]);
        $elena  = User::firstOrCreate(['email' => 'elena@example.com'], [
            'name' => 'Elena Vega', 'password' => bcrypt('password'),
            'role' => 'user', 'telefono' => '928901234', 'direccion' => 'Playa Blanca, Lanzarote',
        ]);
        $rosa   = User::firstOrCreate(['email' => 'rosa@example.com'], [
            'name' => 'Rosa Medina', 'password' => bcrypt('password'),
            'role' => 'user', 'telefono' => '928902345', 'direccion' => 'Costa Teguise, Lanzarote',
        ]);
        $miguel = User::firstOrCreate(['email' => 'miguel@example.com'], [
            'name' => 'Miguel Torres', 'password' => bcrypt('password'),
            'role' => 'user', 'telefono' => '928903456', 'direccion' => 'Arrecife, Lanzarote',
        ]);
        $isabel = User::firstOrCreate(['email' => 'isabel@example.com'], [
            'name' => 'Isabel Romero', 'password' => bcrypt('password'),
            'role' => 'user', 'telefono' => '928904567', 'direccion' => 'Tías, Lanzarote',
        ]);

        // ── IDs reales de categorías (lookup por slug) ───────────────────────
        $cats = \App\Models\Categoria::pluck('id', 'slug');

        // ── Tiendas ──────────────────────────────────────────────────────────
        $tiendas = [

            // ── Frutas y Verduras ────────────────────────────────────────────
            [
                'user_id' => $juan->id, 'categoria_id' => $cats['frutas-y-verduras'],
                'nombre' => 'Finca El Nido', 'slug' => 'finca-el-nido',
                'descripcion' => 'Productos ecológicos de nuestra finca en Haría. Cultivamos con amor y respeto por la tierra.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?w=800',
                'telefono' => '928835001', 'email' => 'info@fincaelnido.com',
                'direccion' => 'Haría, Lanzarote', 'valoracion' => 4.8, 'total_resenas' => 124,
                'latitud' => 29.1455, 'longitud' => -13.5150,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $pedro->id, 'categoria_id' => $cats['frutas-y-verduras'],
                'nombre' => 'Huerta Los Jameos', 'slug' => 'huerta-los-jameos',
                'descripcion' => 'Cultivos en el norte de Lanzarote aprovechando el agua volcánica. Verduras de temporada con certificación ecológica.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800',
                'telefono' => '928836271', 'email' => 'hola@huertalosJameos.com',
                'direccion' => 'Haría, Lanzarote', 'valoracion' => 4.6, 'total_resenas' => 87,
                'latitud' => 29.1520, 'longitud' => -13.4310,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $ana->id, 'categoria_id' => $cats['frutas-y-verduras'],
                'nombre' => 'El Malpaís Verde', 'slug' => 'el-malpais-verde',
                'descripcion' => 'Frutas y verduras cultivadas en suelo volcánico. El mineral único del malpaís da sabores únicos.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=800',
                'telefono' => '928734512', 'email' => 'ventas@malpaisverde.es',
                'direccion' => 'Tinajo, Lanzarote', 'valoracion' => 4.5, 'total_resenas' => 63,
                'latitud' => 29.0651, 'longitud' => -13.7100,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $sofia->id, 'categoria_id' => $cats['frutas-y-verduras'],
                'nombre' => 'La Cesta de Yaiza', 'slug' => 'la-cesta-de-yaiza',
                'descripcion' => 'Cesta semanal de temporada con las mejores verduras del sur de Lanzarote, recién cosechadas.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=800',
                'telefono' => '928836900', 'email' => 'pedidos@cestayaiza.com',
                'direccion' => 'Yaiza, Lanzarote', 'valoracion' => 4.3, 'total_resenas' => 41,
                'latitud' => 28.9560, 'longitud' => -13.7690,
                'activa' => true, 'visible' => true,
            ],

            // ── Carnes (cat 2) ───────────────────────────────────────────────
            [
                'user_id' => $carlos->id, 'categoria_id' => $cats['carnes'],
                'nombre' => 'Carnicería El Volcán', 'slug' => 'carniceria-el-volcan',
                'descripcion' => 'Carnes de cabra majorera y cabrito de Lanzarote criados en libertad en las faldas del volcán.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1529692236671-f1f6cf9683ba?w=800',
                'telefono' => '928746231', 'email' => 'info@carniceriavolcan.com',
                'direccion' => 'San Bartolomé, Lanzarote', 'valoracion' => 4.7, 'total_resenas' => 92,
                'latitud' => 29.0042, 'longitud' => -13.6215,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $luis->id, 'categoria_id' => $cats['carnes'],
                'nombre' => 'La Majada de Teguise', 'slug' => 'la-majada-de-teguise',
                'descripcion' => 'Ganadería tradicional en el corazón de Lanzarote. Ternera, cerdo y cordero con certificado de origen.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=800',
                'telefono' => '928845612', 'email' => 'ventas@majadateguise.com',
                'direccion' => 'Teguise, Lanzarote', 'valoracion' => 4.9, 'total_resenas' => 148,
                'latitud' => 29.0624, 'longitud' => -13.5617,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $elena->id, 'categoria_id' => $cats['carnes'],
                'nombre' => 'Rancho Los Helechos', 'slug' => 'rancho-los-helechos',
                'descripcion' => 'Pequeña granja familiar con cría ecológica de pollo, conejo y cerdo. Sin antibióticos, alimentado con cereales propios.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=800',
                'telefono' => '928736891', 'email' => 'granja@loshelechos.es',
                'direccion' => 'Femés, Lanzarote', 'valoracion' => 4.4, 'total_resenas' => 55,
                'latitud' => 28.9350, 'longitud' => -13.7420,
                'activa' => true, 'visible' => true,
            ],

            // ── Pescados y Mariscos (cat 3) ──────────────────────────────────
            [
                'user_id' => $maria->id, 'categoria_id' => $cats['pescados-y-mariscos'],
                'nombre' => 'Pescadería Mar Azul', 'slug' => 'pescaderia-mar-azul',
                'descripcion' => 'Pescado fresco del día capturado por pescadores locales de Órzola.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1559717865-a99cac1c95d8?w=800',
                'telefono' => '928842123', 'email' => 'info@marazul.com',
                'direccion' => 'Órzola, Lanzarote', 'valoracion' => 4.9, 'total_resenas' => 98,
                'latitud' => 29.2135, 'longitud' => -13.4390,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $pedro->id, 'categoria_id' => $cats['pescados-y-mariscos'],
                'nombre' => 'La Lonja de Arrecife', 'slug' => 'la-lonja-de-arrecife',
                'descripcion' => 'Directamente del puerto al plato. Chopas, vieja, mero y langosta fresca recién desembarcada cada mañana.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1534482421-64566f976cfa?w=800',
                'telefono' => '928810345', 'email' => 'info@lonjarrecife.com',
                'direccion' => 'Arrecife, Lanzarote', 'valoracion' => 4.8, 'total_resenas' => 175,
                'latitud' => 28.9635, 'longitud' => -13.5477,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $carlos->id, 'categoria_id' => $cats['pescados-y-mariscos'],
                'nombre' => 'El Islote del Mar', 'slug' => 'el-islote-del-mar',
                'descripcion' => 'Especialistas en marisco vivo: langostinos, almejas y cangrejos del Atlántico.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1615141982883-c7ad0e69fd62?w=800',
                'telefono' => '928851789', 'email' => 'pedidos@islotedelmar.com',
                'direccion' => 'Puerto del Carmen, Lanzarote', 'valoracion' => 4.6, 'total_resenas' => 64,
                'latitud' => 28.9200, 'longitud' => -13.6580,
                'activa' => true, 'visible' => true,
            ],

            // ── Panadería (cat 4) ────────────────────────────────────────────
            [
                'user_id' => $juan->id, 'categoria_id' => $cats['panaderia'],
                'nombre' => 'Panadería La Tahona', 'slug' => 'panaderia-la-tahona',
                'descripcion' => 'Pan artesanal elaborado con harinas locales y masa madre tradicional.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=800',
                'telefono' => '928815234', 'email' => 'info@latahona.com',
                'direccion' => 'Teguise, Lanzarote', 'valoracion' => 4.7, 'total_resenas' => 156,
                'latitud' => 29.0635, 'longitud' => -13.5620,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $sofia->id, 'categoria_id' => $cats['panaderia'],
                'nombre' => 'Obrador La Caleta', 'slug' => 'obrador-la-caleta',
                'descripcion' => 'Croissants de mantequilla, hogazas de espelta y bollos de anís. Horneamos desde las 5 de la mañana.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=800',
                'telefono' => '928736190', 'email' => 'hola@obradorlacaleta.com',
                'direccion' => 'Playa Blanca, Lanzarote', 'valoracion' => 4.8, 'total_resenas' => 201,
                'latitud' => 28.8634, 'longitud' => -13.8281,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $luis->id, 'categoria_id' => $cats['panaderia'],
                'nombre' => 'El Horno de Haría', 'slug' => 'el-horno-de-haria',
                'descripcion' => 'Pastelería y confitería artesanal. Especialidad en bienmesabe, polvorones y truchas canarias.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=800',
                'telefono' => '928835556', 'email' => 'info@hornoharia.es',
                'direccion' => 'Haría, Lanzarote', 'valoracion' => 4.5, 'total_resenas' => 89,
                'latitud' => 29.1460, 'longitud' => -13.5160,
                'activa' => true, 'visible' => true,
            ],

            // ── Lácteos y Quesos (cat 5) ─────────────────────────────────────
            [
                'user_id' => $ana->id, 'categoria_id' => $cats['lacteos-y-quesos'],
                'nombre' => 'Quesería La Majorera', 'slug' => 'queseria-la-majorera',
                'descripcion' => 'Queso majorero artesanal con leche de cabra criada en libertad. Curado, semicurado y en aceite.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1452195100486-9cc805987862?w=800',
                'telefono' => '928734781', 'email' => 'info@queseriamayorera.com',
                'direccion' => 'Tinajo, Lanzarote', 'valoracion' => 4.9, 'total_resenas' => 312,
                'latitud' => 29.0700, 'longitud' => -13.7050,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $elena->id, 'categoria_id' => $cats['lacteos-y-quesos'],
                'nombre' => 'El Caserío de Femés', 'slug' => 'el-caserio-de-femes',
                'descripcion' => 'Yogures, kéfir y leche fresca de oveja. Elaboración artesanal en una pequeña granja familiar del sur.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=800',
                'telefono' => '928736422', 'email' => 'lacteos@caserioFemes.com',
                'direccion' => 'Femés, Lanzarote', 'valoracion' => 4.6, 'total_resenas' => 73,
                'latitud' => 28.9360, 'longitud' => -13.7400,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $carlos->id, 'categoria_id' => $cats['lacteos-y-quesos'],
                'nombre' => 'Finca Cabra Feliz', 'slug' => 'finca-cabra-feliz',
                'descripcion' => 'Quesos frescos, requesón y mantequilla elaborados con leche de nuestras cabras. Bienestar animal garantizado.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1612187759853-20bfbde129f0?w=800',
                'telefono' => '928747001', 'email' => 'info@cabrafeliz.es',
                'direccion' => 'San Bartolomé, Lanzarote', 'valoracion' => 4.4, 'total_resenas' => 47,
                'latitud' => 29.0050, 'longitud' => -13.6190,
                'activa' => true, 'visible' => true,
            ],

            // ── Vinoteca (cat 6) ─────────────────────────────────────────────
            [
                'user_id' => $maria->id, 'categoria_id' => $cats['vinoteca'],
                'nombre' => 'Bodega Los Volcanes', 'slug' => 'bodega-los-volcanes',
                'descripcion' => 'Vinos de La Geria con denominación de origen Lanzarote. Malvasía blanca, listán negro y moscatel.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1506377247377-2a5b3b417ebb?w=800',
                'telefono' => '928173456', 'email' => 'info@losvolcanes.com',
                'direccion' => 'La Geria, Lanzarote', 'valoracion' => 4.9, 'total_resenas' => 210,
                'latitud' => 28.9850, 'longitud' => -13.6870,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $pedro->id, 'categoria_id' => $cats['vinoteca'],
                'nombre' => 'Viña La Caldera', 'slug' => 'vina-la-caldera',
                'descripcion' => 'Viñedo centenario en hoyos de picón negro, junto al Parque Nacional de Timanfaya. Vinos de terroir único en el mundo.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1474722883778-792e7990302f?w=800',
                'telefono' => '928176234', 'email' => 'vinos@lacaldera.es',
                'direccion' => 'La Geria, Lanzarote', 'valoracion' => 4.8, 'total_resenas' => 183,
                'latitud' => 28.9900, 'longitud' => -13.6950,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $luis->id, 'categoria_id' => $cats['vinoteca'],
                'nombre' => 'El Grifo – Tienda', 'slug' => 'el-grifo-tienda',
                'descripcion' => 'La bodega más antigua de Canarias, fundada en 1775. Vinos premium, licores y visitas guiadas al museo del vino.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=800',
                'telefono' => '928524036', 'email' => 'tienda@elgrifo.com',
                'direccion' => 'San Bartolomé, Lanzarote', 'valoracion' => 5.0, 'total_resenas' => 547,
                'latitud' => 29.0030, 'longitud' => -13.6180,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $sofia->id, 'categoria_id' => $cats['vinoteca'],
                'nombre' => 'Mezclas del Atlántico', 'slug' => 'mezclas-del-atlantico',
                'descripcion' => 'Cervezas artesanales con agua del Atlántico y lúpulo ecológico. Catas y maridajes cada viernes.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1608270586620-248524c67de9?w=800',
                'telefono' => '928720189', 'email' => 'hola@mezclaatlantico.com',
                'direccion' => 'Arrecife, Lanzarote', 'valoracion' => 4.5, 'total_resenas' => 96,
                'latitud' => 28.9640, 'longitud' => -13.5460,
                'activa' => true, 'visible' => true,
            ],

            // ── Artesanía ────────────────────────────────────────────────
            [
                'user_id' => $juan->id, 'categoria_id' => $cats['artesania'],
                'nombre' => 'Taller La Tinaja', 'slug' => 'taller-la-tinaja',
                'descripcion' => 'Cerámica y alfarería tradicional canaria hecha a mano. Piezas únicas inspiradas en los volcanes de Lanzarote.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1565193566173-7a0ee3dbe261?w=800',
                'telefono' => '928835741', 'email' => 'tinaja@artesania.es',
                'direccion' => 'Haría, Lanzarote', 'valoracion' => 4.8, 'total_resenas' => 118,
                'latitud' => 29.1440, 'longitud' => -13.5130,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $ana->id, 'categoria_id' => $cats['artesania'],
                'nombre' => 'Tejidos Volcánicos', 'slug' => 'tejidos-volcanicos',
                'descripcion' => 'Ropa y complementos tejidos a mano con fibras naturales y tintes vegetales. Diseño canario contemporáneo.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?w=800',
                'telefono' => '928736120', 'email' => 'info@tejidosvolcanicos.com',
                'direccion' => 'Teguise, Lanzarote', 'valoracion' => 4.6, 'total_resenas' => 77,
                'latitud' => 29.0610, 'longitud' => -13.5600,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $elena->id, 'categoria_id' => $cats['artesania'],
                'nombre' => 'El Estudio de César', 'slug' => 'el-estudio-de-cesar',
                'descripcion' => 'Arte y souvenirs inspirados en la obra de César Manrique. Impresiones, esculturas en miniatura y joyería de basalto.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1578926288207-a90a103da813?w=800',
                'telefono' => '928812567', 'email' => 'arte@estudiocesar.es',
                'direccion' => 'Arrecife, Lanzarote', 'valoracion' => 4.9, 'total_resenas' => 265,
                'latitud' => 28.9620, 'longitud' => -13.5490,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $carlos->id, 'categoria_id' => $cats['artesania'],
                'nombre' => 'Sal de Janubio', 'slug' => 'sal-de-janubio',
                'descripcion' => 'Sal marina artesanal de las Salinas de Janubio, la laguna salada más grande de Canarias.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800',
                'telefono' => '928736015', 'email' => 'info@saljanubio.com',
                'direccion' => 'Las Salinas, Lanzarote', 'valoracion' => 4.7, 'total_resenas' => 143,
                'latitud' => 28.9130, 'longitud' => -13.7900,
                'activa' => true, 'visible' => true,
            ],

            // ── Frutas y Verduras (extra) ────────────────────────────────────
            [
                'user_id' => $rosa->id, 'categoria_id' => $cats['frutas-y-verduras'],
                'nombre' => 'Cultivos La Tegala', 'slug' => 'cultivos-la-tegala',
                'descripcion' => 'Hortalizas de temporada cultivadas bajo el volcán. Pimientos, tomates y berenjenas de sabor intenso.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1560493676-04071c5f467b?w=800',
                'telefono' => '928845820', 'email' => 'info@cultivoslatgala.com',
                'direccion' => 'Guatiza, Lanzarote', 'valoracion' => 4.4, 'total_resenas' => 38,
                'latitud' => 29.1100, 'longitud' => -13.4850,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $miguel->id, 'categoria_id' => $cats['frutas-y-verduras'],
                'nombre' => 'Agrojardin Masdache', 'slug' => 'agrojardin-masdache',
                'descripcion' => 'Frutas tropicales y subtropicales cultivadas en Masdache. Aguacates, mangos y papayas de Lanzarote.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=800',
                'telefono' => '928524710', 'email' => 'pedidos@agrojardinmasdache.es',
                'direccion' => 'Masdache, Lanzarote', 'valoracion' => 4.6, 'total_resenas' => 52,
                'latitud' => 29.0210, 'longitud' => -13.6650,
                'activa' => true, 'visible' => true,
            ],

            // ── Carnes (extra) ───────────────────────────────────────────────
            [
                'user_id' => $isabel->id, 'categoria_id' => $cats['carnes'],
                'nombre' => 'La Cabaña de Tías', 'slug' => 'la-cabana-de-tias',
                'descripcion' => 'Embutidos artesanales: chorizo, morcilla y salchichón elaborados con recetas tradicionales canarias.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=800',
                'telefono' => '928834562', 'email' => 'embutidos@cabanatias.com',
                'direccion' => 'Tías, Lanzarote', 'valoracion' => 4.5, 'total_resenas' => 67,
                'latitud' => 28.9620, 'longitud' => -13.6370,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $rosa->id, 'categoria_id' => $cats['carnes'],
                'nombre' => 'Carnicería Costa Teguise', 'slug' => 'carniceria-costa-teguise',
                'descripcion' => 'Carne fresca y de calidad para todos los bolsillos. Especialidad en cabra en salmorejo y costillas al mojo.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800',
                'telefono' => '928592310', 'email' => 'carniceria@cosatateguise.com',
                'direccion' => 'Costa Teguise, Lanzarote', 'valoracion' => 4.3, 'total_resenas' => 44,
                'latitud' => 29.0041, 'longitud' => -13.4970,
                'activa' => true, 'visible' => true,
            ],

            // ── Pescados y Mariscos (extra) ──────────────────────────────────
            [
                'user_id' => $miguel->id, 'categoria_id' => $cats['pescados-y-mariscos'],
                'nombre' => 'El Barco de Playa Blanca', 'slug' => 'el-barco-de-playa-blanca',
                'descripcion' => 'Pesca artesanal del sur de Lanzarote. Salemas, abades y bocinegros recién sacados del agua.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?w=800',
                'telefono' => '928518990', 'email' => 'pesca@barcoplayablanca.com',
                'direccion' => 'Playa Blanca, Lanzarote', 'valoracion' => 4.7, 'total_resenas' => 81,
                'latitud' => 28.8680, 'longitud' => -13.8260,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $isabel->id, 'categoria_id' => $cats['pescados-y-mariscos'],
                'nombre' => 'Pulpería La Graciosa', 'slug' => 'pulperia-la-graciosa',
                'descripcion' => 'Pulpo y chipirones frescos de La Graciosa. También mejillones, berberechos y navajas del Atlántico norte.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1498654200943-1088dd4438ae?w=800',
                'telefono' => '928841225', 'email' => 'pedidos@pulperiagraciosa.es',
                'direccion' => 'Órzola, Lanzarote', 'valoracion' => 4.8, 'total_resenas' => 109,
                'latitud' => 29.2080, 'longitud' => -13.4520,
                'activa' => true, 'visible' => true,
            ],

            // ── Panadería (extra) ────────────────────────────────────────────
            [
                'user_id' => $miguel->id, 'categoria_id' => $cats['panaderia'],
                'nombre' => 'Dulcería La Palma', 'slug' => 'dulceria-la-palma',
                'descripcion' => 'Repostería fina y tartas personalizadas para bodas y eventos. Pastelería con ingredientes km0.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1464349095431-e9a21285b5f3?w=800',
                'telefono' => '928812045', 'email' => 'tartas@dulceriapalma.com',
                'direccion' => 'Arrecife, Lanzarote', 'valoracion' => 4.9, 'total_resenas' => 187,
                'latitud' => 28.9580, 'longitud' => -13.5510,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $rosa->id, 'categoria_id' => $cats['panaderia'],
                'nombre' => 'Pan del Volcán', 'slug' => 'pan-del-volcan',
                'descripcion' => 'Panes de escaldón, gofio y centeno. Recetas ancestrales que mantienen viva la tradición panadera canaria.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=800',
                'telefono' => '928841799', 'email' => 'pan@pandelvolcan.es',
                'direccion' => 'Haría, Lanzarote', 'valoracion' => 4.6, 'total_resenas' => 73,
                'latitud' => 29.1420, 'longitud' => -13.5200,
                'activa' => true, 'visible' => true,
            ],

            // ── Lácteos y Quesos (extra) ─────────────────────────────────────
            [
                'user_id' => $miguel->id, 'categoria_id' => $cats['lacteos-y-quesos'],
                'nombre' => 'Granja Los Picos', 'slug' => 'granja-los-picos',
                'descripcion' => 'Mantequillas, natas y quesos de autor elaborados con leche cruda de vaca de raza canaria.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=800',
                'telefono' => '928846320', 'email' => 'lacteos@granjalospicos.com',
                'direccion' => 'San Bartolomé, Lanzarote', 'valoracion' => 4.5, 'total_resenas' => 59,
                'latitud' => 29.0090, 'longitud' => -13.6100,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $isabel->id, 'categoria_id' => $cats['lacteos-y-quesos'],
                'nombre' => 'La Vaquería de Tías', 'slug' => 'la-vaqueria-de-tias',
                'descripcion' => 'Leche pasteurizada y ultrafresh, batidos naturales y helados artesanales de temporada.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=800',
                'telefono' => '928834005', 'email' => 'pedidos@vaqueriatias.com',
                'direccion' => 'Tías, Lanzarote', 'valoracion' => 4.4, 'total_resenas' => 48,
                'latitud' => 28.9580, 'longitud' => -13.6420,
                'activa' => true, 'visible' => true,
            ],

            // ── Vinoteca (extra) ─────────────────────────────────────────────
            [
                'user_id' => $rosa->id, 'categoria_id' => $cats['vinoteca'],
                'nombre' => 'Ron y Mojo Costa Teguise', 'slug' => 'ron-y-mojo-costa-teguise',
                'descripcion' => 'Rones artesanales de caña y mojos en todas sus variedades: rojo, verde, cilantro y almendra.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800',
                'telefono' => '928592780', 'email' => 'info@ronymojoct.com',
                'direccion' => 'Costa Teguise, Lanzarote', 'valoracion' => 4.6, 'total_resenas' => 112,
                'latitud' => 28.9980, 'longitud' => -13.5010,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $isabel->id, 'categoria_id' => $cats['vinoteca'],
                'nombre' => 'Bodega Rubicón', 'slug' => 'bodega-rubicon',
                'descripcion' => 'Viñedo del sur de Lanzarote con vistas al Timanfaya. Tintos y rosados de listán negro y syrah volcánico.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1553361371-9b22f78e8b1d?w=800',
                'telefono' => '928173812', 'email' => 'visitas@bodegarubicon.com',
                'direccion' => 'La Geria, Lanzarote', 'valoracion' => 4.8, 'total_resenas' => 165,
                'latitud' => 28.9820, 'longitud' => -13.7010,
                'activa' => true, 'visible' => true,
            ],

            // ── Artesanía (extra) ────────────────────────────────────────────
            [
                'user_id' => $rosa->id, 'categoria_id' => $cats['artesania'],
                'nombre' => 'Joyería Volcánica', 'slug' => 'joyeria-volcanica',
                'descripcion' => 'Joyas únicas fabricadas con basalto, lava y vidrio volcánico de Lanzarote. Collares, pendientes y pulseras.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1611652022419-a9419f74343d?w=800',
                'telefono' => '928592445', 'email' => 'joyas@joyeriavolcanica.com',
                'direccion' => 'Costa Teguise, Lanzarote', 'valoracion' => 4.8, 'total_resenas' => 134,
                'latitud' => 28.9990, 'longitud' => -13.4980,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $miguel->id, 'categoria_id' => $cats['artesania'],
                'nombre' => 'Casa de los Telares', 'slug' => 'casa-de-los-telares',
                'descripcion' => 'Mantelería, alfombras y tapices tejidos a mano con técnicas transmitidas de generación en generación.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=800',
                'telefono' => '928845990', 'email' => 'telares@casadelastelares.com',
                'direccion' => 'Teguise, Lanzarote', 'valoracion' => 4.7, 'total_resenas' => 89,
                'latitud' => 29.0580, 'longitud' => -13.5580,
                'activa' => true, 'visible' => true,
            ],
            [
                'user_id' => $isabel->id, 'categoria_id' => $cats['artesania'],
                'nombre' => 'Taller Mojo Picón', 'slug' => 'taller-mojo-picon',
                'descripcion' => 'Salsas y condimentos artesanales: mojo rojo, verde, cilantro y escabeche. Envasados sin conservantes.',
                'imagen_portada' => 'https://images.unsplash.com/photo-1582169296194-e4d644c48063?w=800',
                'telefono' => '928834780', 'email' => 'pedidos@tallermojopicon.com',
                'direccion' => 'Puerto del Carmen, Lanzarote', 'valoracion' => 4.9, 'total_resenas' => 221,
                'latitud' => 28.9230, 'longitud' => -13.6610,
                'activa' => true, 'visible' => true,
            ],
        ];

        foreach ($tiendas as $datos) {
            Tienda::firstOrCreate(['slug' => $datos['slug']], array_merge($datos, ['logo' => '/images/logo.png']));
        }
    }
}
