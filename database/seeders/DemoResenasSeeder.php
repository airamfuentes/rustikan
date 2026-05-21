<?php

namespace Database\Seeders;

use App\Models\Resena;
use App\Models\Tienda;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea reseñas escritas y realistas para todas las tiendas demo.
 * Recalcula valoracion y total_resenas en cada tienda al terminar.
 * Idempotente: elimina reseñas previas de usuarios demo antes de insertar.
 */
class DemoResenasSeeder extends Seeder
{
    public function run(): void
    {
        $tiendas  = Tienda::all();
        $usuarios = User::where('role', 'user')->pluck('id');

        if ($tiendas->isEmpty() || $usuarios->isEmpty()) {
            $this->command?->warn('Asegúrate de ejecutar DemoUsersSeeder y DemoTiendasSeeder antes de DemoResenasSeeder.');
            return;
        }

        $templates = [
            'Producto excelente, volveré a comprar.',
            'Llegó muy fresco y bien embalado.',
            'La calidad supera lo esperado por el precio.',
            'Buena comunicación y entrega puntual.',
            'Recomendable para regalos y consumo diario.',
            'Pedí por curiosidad y se convirtió en mi tienda favorita.',
        ];

        $totalCreadas = 0;

        foreach ($tiendas as $tienda) {
            // eliminar reseñas previas generadas por demo para evitar duplicados
            Resena::where('tienda_id', $tienda->id)->delete();

            $n = rand(3, 6);
            for ($i = 0; $i < $n; $i++) {
                $userId = $usuarios->values()[$i % $usuarios->count()];
                Resena::create([
                    'user_id'    => $userId,
                    'tienda_id'  => $tienda->id,
                    'puntuacion' => rand(3, 5),
                    'titulo'     => collect($templates)->random(),
                    'comentario' => collect($templates)->random() . ' ' . collect($templates)->random(),
                    'created_at' => now()->subDays(rand(1, 180))->subHours(rand(0, 23)),
                    'updated_at' => now()->subDays(rand(0, 5)),
                ]);
                $totalCreadas++;
            }

            // Recalcular valoración y total_resenas de la tienda
            $stats = Resena::where('tienda_id', $tienda->id)
                ->selectRaw('COUNT(*) as total, AVG(puntuacion) as media')
                ->first();

            $tienda->update([
                'total_resenas' => $stats->total ?? 0,
                'valoracion'    => round($stats->media ?? 0, 2),
            ]);
        }

        $this->command?->info("DemoResenasSeeder: {$totalCreadas} reseñas creadas y valoraciones recalculadas.");
    }
}
