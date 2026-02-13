<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productos = [
            // Finca El Nido (Tienda 1)
            [
                'tienda_id' => 1,
                'categoria_id' => 1,
                'nombre' => 'Tomates Cherry',
                'slug' => 'tomates-cherry',
                'descripcion' => 'Tomates cherry ecológicos, dulces y jugosos',
                'precio' => 3.50,
                'unidad' => 'kg',
                'imagen' => 'https://images.unsplash.com/photo-1592841200221-a6898f307baa?w=400',
                'stock' => 50,
                'stock_minimo' => 10,
                'disponible' => true,
                'destacado' => true,
            ],
            [
                'tienda_id' => 1,
                'categoria_id' => 1,
                'nombre' => 'Lechugas Mixtas',
                'slug' => 'lechugas-mixtas',
                'descripcion' => 'Variedad de lechugas frescas del día',
                'precio' => 2.00,
                'unidad' => 'unidad',
                'imagen' => 'https://images.unsplash.com/photo-1622206151226-18ca2c9ab4a1?w=400',
                'stock' => 30,
                'stock_minimo' => 5,
                'disponible' => true,
                'destacado' => false,
            ],
            [
                'tienda_id' => 1,
                'categoria_id' => 1,
                'nombre' => 'Zanahorias',
                'slug' => 'zanahorias',
                'descripcion' => 'Zanahorias ecológicas recién cosechadas',
                'precio' => 2.50,
                'precio_oferta' => 1.99,
                'unidad' => 'kg',
                'imagen' => 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=400',
                'stock' => 40,
                'stock_minimo' => 8,
                'disponible' => true,
                'destacado' => true,
            ],
            // Pescadería Mar Azul (Tienda 2)
            [
                'tienda_id' => 2,
                'categoria_id' => 3,
                'nombre' => 'Vieja Fresca',
                'slug' => 'vieja-fresca',
                'descripcion' => 'Vieja del día, pescado típico canario',
                'precio' => 18.50,
                'unidad' => 'kg',
                'imagen' => 'https://images.unsplash.com/photo-1544943910-4c1dc44aab44?w=400',
                'stock' => 15,
                'stock_minimo' => 3,
                'disponible' => true,
                'destacado' => true,
            ],
            [
                'tienda_id' => 2,
                'categoria_id' => 3,
                'nombre' => 'Calamares',
                'slug' => 'calamares',
                'descripcion' => 'Calamares frescos de Lanzarote',
                'precio' => 12.00,
                'unidad' => 'kg',
                'imagen' => 'https://images.unsplash.com/photo-1599084993091-1cb5c0721cc6?w=400',
                'stock' => 20,
                'stock_minimo' => 5,
                'disponible' => true,
                'destacado' => false,
            ],
            // Panadería La Tahona (Tienda 3)
            [
                'tienda_id' => 3,
                'categoria_id' => 4,
                'nombre' => 'Pan de Millo',
                'slug' => 'pan-de-millo',
                'descripcion' => 'Pan tradicional canario de millo (maíz)',
                'precio' => 2.80,
                'unidad' => 'unidad',
                'imagen' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400',
                'stock' => 40,
                'stock_minimo' => 10,
                'disponible' => true,
                'destacado' => true,
            ],
            [
                'tienda_id' => 3,
                'categoria_id' => 4,
                'nombre' => 'Empanadas Caseras',
                'slug' => 'empanadas-caseras',
                'descripcion' => 'Empanadas rellenas de atún o carne',
                'precio' => 1.50,
                'unidad' => 'unidad',
                'imagen' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=400',
                'stock' => 25,
                'stock_minimo' => 8,
                'disponible' => true,
                'destacado' => false,
            ],
            // Bodega Los Volcanes (Tienda 4)
            [
                'tienda_id' => 4,
                'categoria_id' => 6,
                'nombre' => 'Malvasía Volcánica',
                'slug' => 'malvasia-volcanica',
                'descripcion' => 'Vino blanco DO Lanzarote, uva Malvasía',
                'precio' => 12.50,
                'unidad' => 'botella',
                'imagen' => 'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=400',
                'stock' => 60,
                'stock_minimo' => 15,
                'disponible' => true,
                'destacado' => true,
            ],
            [
                'tienda_id' => 4,
                'categoria_id' => 6,
                'nombre' => 'Listán Negro',
                'slug' => 'listan-negro',
                'descripcion' => 'Vino tinto joven de La Geria',
                'precio' => 10.00,
                'unidad' => 'botella',
                'imagen' => 'https://images.unsplash.com/photo-1547595628-c61a29f496f0?w=400',
                'stock' => 45,
                'stock_minimo' => 10,
                'disponible' => true,
                'destacado' => false,
            ],
        ];

        foreach ($productos as $producto) {
            \App\Models\Producto::create($producto);
        }
    }
}
