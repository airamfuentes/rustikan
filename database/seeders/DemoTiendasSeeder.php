<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Tienda;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoTiendasSeeder extends Seeder
{
    public function run(): void
    {
        $cats   = Categoria::pluck('id', 'slug');
        $owners = User::where('role', 'owner')->pluck('id', 'email');

        $tiendas = [
            [
                'owner_email'    => 'domingo@finca-el-nido.es',
                'cat'            => 'frutas-y-verduras',
                'nombre'         => 'Finca El Nido',
                'slug'           => 'finca-el-nido',
                'descripcion'    => 'Cultivo ecológico en tierras de Tinajo. Tomate canario, papa negra, batata y verdura de temporada recolectada el mismo día del envío.',
                'telefono'       => '928840100',
                'email'          => 'pedidos@finca-el-nido.es',
                'direccion'      => 'Camino del Volcán s/n, Tinajo, Lanzarote',
                'latitud'        => 29.069800,
                'longitud'       => -13.674400,
                'logo'           => null,
                'imagen_portada' => null,
            ],
            [
                'owner_email'    => 'maria@huerta-jameos.es',
                'cat'            => 'frutas-y-verduras',
                'nombre'         => 'Huerta Los Jameos',
                'slug'           => 'huerta-los-jameos',
                'descripcion'    => 'Productores en Haría desde hace tres generaciones. Especialistas en cebolla morada, calabaza canaria y pimientos palmeros.',
                'telefono'       => '928835200',
                'email'          => 'huerta@losjameos.es',
                'direccion'      => 'Camino de Los Jameos 3, Haría, Lanzarote',
                'latitud'        => 29.143150,
                'longitud'       => -13.498800,
                'logo'           => null,
                'imagen_portada' => null,
            ],
            [
                'owner_email'    => 'antonio@bodega-geria.es',
                'cat'            => 'vinoteca',
                'nombre'         => 'Bodega La Geria',
                'slug'           => 'bodega-la-geria',
                'descripcion'    => 'Vinos volcánicos de uva Malvasía Volcánica cultivada en los hoyos característicos del Parque Natural de La Geria.',
                'telefono'       => '928173178',
                'email'          => 'info@bodega-geria.es',
                'direccion'      => 'Carretera de La Geria s/n, km 11, Yaiza, Lanzarote',
                'latitud'        => 28.985430,
                'longitud'       => -13.745670,
                'logo'           => null,
                'imagen_portada' => null,
            ],
            [
                'owner_email'    => 'antonio@bodega-geria.es',
                'cat'            => 'vinoteca',
                'nombre'         => 'Bodega Stratvs',
                'slug'           => 'bodega-stratvs-demo',
                'descripcion'    => 'Bodega boutique en La Geria. Producción limitada de Malvasía Seca y Moscatel Diego, con visitas guiadas y catas.',
                'telefono'       => '928809977',
                'email'          => 'reservas@stratvs.es',
                'direccion'      => 'Carretera de La Geria km 18, Yaiza, Lanzarote',
                'latitud'        => 28.972110,
                'longitud'       => -13.736220,
                'logo'           => null,
                'imagen_portada' => null,
            ],
            [
                'owner_email'    => 'carmen@quesos-majorero.es',
                'cat'            => 'lacteos-y-quesos',
                'nombre'         => 'Quesos Doña Carmen',
                'slug'           => 'quesos-dona-carmen',
                'descripcion'    => 'Queso de cabra majorero artesanal, con D.O.P. Curados, semicurados y tiernos elaborados en pequeñas tandas.',
                'telefono'       => '928848101',
                'email'          => 'carmen@quesos-majorero.es',
                'direccion'      => 'C/ Real 45, Teguise, Lanzarote',
                'latitud'        => 29.060500,
                'longitud'       => -13.561380,
                'logo'           => null,
                'imagen_portada' => null,
            ],
            [
                'owner_email'    => 'carmen@quesos-majorero.es',
                'cat'            => 'lacteos-y-quesos',
                'nombre'         => 'Lácteos Conejera',
                'slug'           => 'lacteos-conejera',
                'descripcion'    => 'Yogures, requesón y leche fresca de cabras criadas en libertad en el norte de Lanzarote.',
                'telefono'       => '928848202',
                'email'          => 'lacteos@conejera.es',
                'direccion'      => 'Caleta de Famara s/n, Teguise, Lanzarote',
                'latitud'        => 29.122000,
                'longitud'       => -13.557600,
                'logo'           => null,
                'imagen_portada' => null,
            ],
            [
                'owner_email'    => 'sergio@pescados-arrecife.es',
                'cat'            => 'pescados-y-mariscos',
                'nombre'         => 'Pescados El Charco',
                'slug'           => 'pescados-el-charco',
                'descripcion'    => 'Pescado fresco descargado a diario en el puerto de Arrecife. Vieja, sama, cherne y pulpo canario.',
                'telefono'       => '928804500',
                'email'          => 'pedidos@pescados-elcharco.es',
                'direccion'      => 'Muelle Comercial, Arrecife, Lanzarote',
                'latitud'        => 28.957500,
                'longitud'       => -13.547200,
                'logo'           => null,
                'imagen_portada' => null,
            ],
            [
                'owner_email'    => 'sergio@pescados-arrecife.es',
                'cat'            => 'pescados-y-mariscos',
                'nombre'         => 'Mariscos Punta Mujeres',
                'slug'           => 'mariscos-punta-mujeres',
                'descripcion'    => 'Pulpo, lapas y bocinegros recogidos en la costa norte. Selección artesanal de marisco fresco.',
                'telefono'       => '928835650',
                'email'          => 'punta@mariscos.es',
                'direccion'      => 'Av. Marítima 8, Punta Mujeres, Haría, Lanzarote',
                'latitud'        => 29.149260,
                'longitud'       => -13.464950,
                'logo'           => null,
                'imagen_portada' => null,
            ],
            [
                'owner_email'    => 'lucia@panaderia-norte.es',
                'cat'            => 'panaderia',
                'nombre'         => 'Panadería del Norte',
                'slug'           => 'panaderia-del-norte',
                'descripcion'    => 'Pan de millo (maíz), bizcochón lanzaroteño y rosquetes elaborados a diario con harinas locales.',
                'telefono'       => '928835100',
                'email'          => 'horno@panaderia-norte.es',
                'direccion'      => 'C/ Iglesia 8, Haría, Lanzarote',
                'latitud'        => 29.144070,
                'longitud'       => -13.500140,
                'logo'           => null,
                'imagen_portada' => null,
            ],
            [
                'owner_email'    => 'lucia@panaderia-norte.es',
                'cat'            => 'panaderia',
                'nombre'         => 'Horno La Geria',
                'slug'           => 'horno-la-geria',
                'descripcion'    => 'Repostería tradicional canaria: queques, suspiros de mojo y truchas de batata para ocasiones especiales.',
                'telefono'       => '928174333',
                'email'          => 'horno@lageria.es',
                'direccion'      => 'C/ Las Quemadas 12, Yaiza, Lanzarote',
                'latitud'        => 28.961700,
                'longitud'       => -13.751900,
                'logo'           => null,
                'imagen_portada' => null,
            ],
            [
                'owner_email'    => 'javier@artesanos-lanzarote.es',
                'cat'            => 'artesania',
                'nombre'         => 'Artesanos de Teguise',
                'slug'           => 'artesanos-de-teguise',
                'descripcion'    => 'Cerámica tradicional, timples y trabajos en piedra volcánica realizados por artesanos locales en la Villa de Teguise.',
                'telefono'       => '928845800',
                'email'          => 'taller@artesanos-lanzarote.es',
                'direccion'      => 'Plaza de la Constitución 4, Teguise, Lanzarote',
                'latitud'        => 29.060860,
                'longitud'       => -13.561200,
                'logo'           => null,
                'imagen_portada' => null,
            ],
            [
                'owner_email'    => 'javier@artesanos-lanzarote.es',
                'cat'            => 'artesania',
                'nombre'         => 'Cestería Mancha Blanca',
                'slug'           => 'cesteria-mancha-blanca',
                'descripcion'    => 'Cestas de palma, esteras y sombreros canarios trenzados a mano en el taller familiar de Mancha Blanca.',
                'telefono'       => '928840322',
                'email'          => 'cesteria@manchablanca.es',
                'direccion'      => 'C/ Las Hoyas 5, Mancha Blanca, Tinajo, Lanzarote',
                'latitud'        => 29.052800,
                'longitud'       => -13.671900,
                'logo'           => null,
                'imagen_portada' => null,
            ],
        ];

        foreach ($tiendas as $t) {
            $userId = $owners[$t['owner_email']] ?? null;
            $catId  = $cats[$t['cat']] ?? null;
            if (!$userId || !$catId) continue;

            Tienda::updateOrCreate(
                ['slug' => $t['slug']],
                [
                    'user_id'        => $userId,
                    'categoria_id'   => $catId,
                    'nombre'         => $t['nombre'],
                    'descripcion'    => $t['descripcion'],
                    'logo'           => $t['logo'],
                    'imagen_portada' => $t['imagen_portada'],
                    'telefono'       => $t['telefono'],
                    'email'          => $t['email'],
                    'direccion'      => $t['direccion'],
                    'latitud'        => $t['latitud'],
                    'longitud'       => $t['longitud'],
                    'activa'         => true,
                    'visible'        => true,
                ]
            );
        }

        $this->command?->info('DemoTiendasSeeder: ' . count($tiendas) . ' tiendas creadas/actualizadas.');
    }
}
