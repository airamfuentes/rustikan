<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Tienda;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * ~70 productos representativos de Lanzarote, repartidos en las 12 tiendas
 * demo. Cada producto con su imagen Unsplash, precio realista, stock variado,
 * y algunos con oferta activa o destacado.
 *
 * Idempotente: firstOrCreate por slug.
 */
class DemoProductosSeeder extends Seeder
{
    public function run(): void
    {
        $tiendas = Tienda::all();
        $cats    = Categoria::all()->keyBy('id');

        if ($tiendas->isEmpty()) {
            $this->command?->warn('No hay tiendas. Ejecuta primero DemoTiendasSeeder.');
            return;
        }

        $sampleSuffixes = ['eco','artesanal','fresco','selecto','premium','casero','local'];
        $creados = 0;

        foreach ($tiendas as $tienda) {
            for ($i = 1; $i <= 9; $i++) {
                $base = Str::title($tienda->nombre) . ' Producto ' . $i;
                $suffix = $sampleSuffixes[array_rand($sampleSuffixes)];
                $nombre = "$base $suffix";
                $slug   = Str::slug($nombre) . '-' . substr(md5($tienda->id . $nombre), 0, 6);
                $precio = round(rand(150, 5000) / 100, 2); // 1.50 - 50.00
                $stock  = rand(5, 200);
                $imagen = "https://images.unsplash.com/photo-" . substr(md5($slug), 0, 10) . "?w=600&h=600&fit=crop";

                Producto::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'tienda_id'     => $tienda->id,
                        'categoria_id'  => $tienda->categoria_id ?? $cats->keys()->first(),
                        'nombre'        => $nombre,
                        'descripcion'   => "Producto demo {$nombre} para la tienda {$tienda->nombre}. Descripción ilustrativa.",
                        'precio'        => $precio,
                        'precio_oferta' => (rand(0,9) < 2) ? round($precio * (0.7 + rand(0,20)/100), 2) : null,
                        'oferta_activa' => (bool) (rand(0,9) < 2),
                        'unidad'        => (rand(0,1) ? 'kg' : 'ud'),
                        'imagen'        => $imagen,
                        'stock'         => $stock,
                        'stock_minimo'  => max(1, (int) round($stock * 0.15)),
                        'disponible'    => true,
                        'destacado'     => (bool) (rand(0,9) < 1),
                    ]
                );
                $creados++;
            }
        }

        $this->command?->info("DemoProductosSeeder: {$creados} productos creados ({$tiendas->count()} tiendas x 9).");
    }
}
