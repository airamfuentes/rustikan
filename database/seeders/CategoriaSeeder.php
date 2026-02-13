<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Frutas y Verduras',
                'slug' => 'frutas-y-verduras',
                'icono' => '🍎',
                'descripcion' => 'Frutas y verduras frescas de productores locales',
            ],
            [
                'nombre' => 'Carnes',
                'slug' => 'carnes',
                'icono' => '🥩',
                'descripcion' => 'Carnes frescas y de calidad',
            ],
            [
                'nombre' => 'Pescados y Mariscos',
                'slug' => 'pescados-y-mariscos',
                'icono' => '🐟',
                'descripcion' => 'Pescado fresco del día',
            ],
            [
                'nombre' => 'Panadería',
                'slug' => 'panaderia',
                'icono' => '🥖',
                'descripcion' => 'Pan y bollería artesanal',
            ],
            [
                'nombre' => 'Lácteos y Quesos',
                'slug' => 'lacteos-y-quesos',
                'icono' => '🧀',
                'descripcion' => 'Productos lácteos y quesos artesanales',
            ],
            [
                'nombre' => 'Vinos y Bebidas',
                'slug' => 'vinos-y-bebidas',
                'icono' => '🍷',
                'descripcion' => 'Vinos locales y bebidas artesanales',
            ],
            [
                'nombre' => 'Conservas y Mermeladas',
                'slug' => 'conservas-y-mermeladas',
                'icono' => '🫙',
                'descripcion' => 'Conservas y mermeladas caseras',
            ],
            [
                'nombre' => 'Artesanía',
                'slug' => 'artesania',
                'icono' => '🏺',
                'descripcion' => 'Productos artesanales de Lanzarote',
            ],
        ];

        foreach ($categorias as $categoria) {
            \App\Models\Categoria::create($categoria);
        }
    }
}
