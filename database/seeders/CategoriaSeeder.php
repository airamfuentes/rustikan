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
            ['nombre' => 'Frutas y Verduras',   'slug' => 'frutas-y-verduras',   'icono' => 'frutas-y-verduras',   'descripcion' => 'Frutas y verduras frescas de productores locales'],
            ['nombre' => 'Carnes',              'slug' => 'carnes',              'icono' => 'carnes',              'descripcion' => 'Carnes frescas y de calidad'],
            ['nombre' => 'Pescados y Mariscos', 'slug' => 'pescados-y-mariscos', 'icono' => 'pescados-y-mariscos', 'descripcion' => 'Pescado fresco del día'],
            ['nombre' => 'Panadería',           'slug' => 'panaderia',           'icono' => 'panaderia',           'descripcion' => 'Pan y bollería artesanal'],
            ['nombre' => 'Lácteos y Quesos',    'slug' => 'lacteos-y-quesos',    'icono' => 'lacteos-y-quesos',    'descripcion' => 'Productos lácteos y quesos artesanales'],
            ['nombre' => 'Vinoteca',            'slug' => 'vinoteca',            'icono' => 'vinoteca',            'descripcion' => 'Vinos locales y bebidas artesanales'],
            ['nombre' => 'Artesanía',           'slug' => 'artesania',           'icono' => 'artesania',           'descripcion' => 'Productos artesanales de Lanzarote'],
        ];

        foreach ($categorias as $categoria) {
            \App\Models\Categoria::firstOrCreate(['slug' => $categoria['slug']], $categoria);
        }
    }
}
