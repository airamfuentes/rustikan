<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Tienda;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoTiendasSeeder extends Seeder
{
    public function run(): void
    {
        $cats   = Categoria::all();
        $owners = User::where('role', 'owner')->pluck('id');

        if ($cats->isEmpty()) {
            $this->command?->warn('No hay categorías. Ejecuta primero CategoriaSeeder.');
            return;
        }
        if ($owners->isEmpty()) {
            $this->command?->warn('No hay owners. Ejecuta primero DemoUsersSeeder.');
            return;
        }

        $created = 0;
        foreach ($cats as $cat) {
            for ($i = 1; $i <= 10; $i++) {
                $ownerId = $owners->values()[(($created + $i) - 1) % $owners->count()];
                $nombre  = sprintf('%s Tienda %02d', $cat->nombre, $i);
                $slug    = Str::slug($cat->slug . '-tienda-' . $i . '-' . $ownerId);
                $telefono = '6' . str_pad((string)(29000000 + $created + $i), 8, '0', STR_PAD_LEFT);
                $email    = Str::slug($slug) . '@rustikan-demo.es';
                $imagen   = "https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1200&h=400&fit=crop&auto=format&ixlib=rb-1.2.1&q=80&sat=0&exp=" . ($created + $i);

                Tienda::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'user_id'        => $ownerId,
                        'categoria_id'   => $cat->id,
                        'nombre'         => $nombre,
                        'descripcion'    => "Productos locales de la categoría {$cat->nombre}. Tienda demo.",
                        'logo'           => null,
                        'imagen_portada' => $imagen,
                        'telefono'       => $telefono,
                        'email'          => $email,
                        'direccion'      => 'Dirección demo, Lanzarote',
                        'latitud'        => 29.0 + ($created % 10) * 0.01,
                        'longitud'       => -13.5 - ($created % 10) * 0.01,
                        'activa'         => true,
                        'visible'        => true,
                    ]
                );
                $created++;
            }
        }

        $this->command?->info("DemoTiendasSeeder: {$created} tiendas creadas/actualizadas.");
    }
}
