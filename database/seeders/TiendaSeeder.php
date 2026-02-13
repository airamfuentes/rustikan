<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TiendaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $juan = \App\Models\User::where('email', 'juan@example.com')->first();
        $maria = \App\Models\User::where('email', 'maria@example.com')->first();

        $tiendas = [
            [
                'user_id' => $juan->id,
                'categoria_id' => 1,
                'nombre' => 'Finca El Nido',
                'slug' => 'finca-el-nido',
                'descripcion' => 'Productos ecológicos de nuestra finca en Haría. Cultivamos con amor y respeto por la tierra.',
                'logo' => '/images/logo.png',
                'imagen_portada' => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?w=800',
                'telefono' => '928835001',
                'email' => 'info@fincaelnido.com',
                'direccion' => 'Haría, Lanzarote',
                'valoracion' => 4.8,
                'total_resenas' => 124,
                'activa' => true,
                'visible' => true,
            ],
            [
                'user_id' => $maria->id,
                'categoria_id' => 3,
                'nombre' => 'Pescadería Mar Azul',
                'slug' => 'pescaderia-mar-azul',
                'descripcion' => 'Pescado fresco del día capturado por pescadores locales de Órzola.',
                'logo' => '/images/logo.png',
                'imagen_portada' => 'https://images.unsplash.com/photo-1559717865-a99cac1c95d8?w=800',
                'telefono' => '928842123',
                'email' => 'info@marazul.com',
                'direccion' => 'Órzola, Lanzarote',
                'valoracion' => 4.9,
                'total_resenas' => 98,
                'activa' => true,
                'visible' => true,
            ],
            [
                'user_id' => $juan->id,
                'categoria_id' => 4,
                'nombre' => 'Panadería La Tahona',
                'slug' => 'panaderia-la-tahona',
                'descripcion' => 'Pan artesanal elaborado con harinas locales y masa madre tradicional.',
                'logo' => '/images/logo.png',
                'imagen_portada' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=800',
                'telefono' => '928815234',
                'email' => 'info@latahona.com',
                'direccion' => 'Teguise, Lanzarote',
                'valoracion' => 4.7,
                'total_resenas' => 156,
                'activa' => true,
                'visible' => true,
            ],
            [
                'user_id' => $maria->id,
                'categoria_id' => 6,
                'nombre' => 'Bodega Los Volcanes',
                'slug' => 'bodega-los-volcanes',
                'descripcion' => 'Vinos de La Geria con denominación de origen Lanzarote.',
                'logo' => '/images/logo.png',
                'imagen_portada' => 'https://images.unsplash.com/photo-1506377247377-2a5b3b417ebb?w=800',
                'telefono' => '928173456',
                'email' => 'info@losvolcanes.com',
                'direccion' => 'La Geria, Lanzarote',
                'valoracion' => 4.9,
                'total_resenas' => 210,
                'activa' => true,
                'visible' => true,
            ],
        ];

        foreach ($tiendas as $tienda) {
            \App\Models\Tienda::create($tienda);
        }
    }
}
