<?php

namespace App\Console\Commands;

use App\Models\Producto;
use App\Models\Tienda;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Limpia archivos del disco "public" que ya no están referenciados por ningún
 * registro de la BD. Por defecto hace dry-run; usa --force para borrar de verdad.
 *
 *   php artisan storage:clean-orphans            (dry-run, lista lo que borraría)
 *   php artisan storage:clean-orphans --force    (borra)
 *   php artisan storage:clean-orphans --force --quiet (cron-friendly)
 */
class CleanOrphanStorage extends Command
{
    protected $signature = 'storage:clean-orphans
        {--force : Borrar de verdad (sin esta flag solo lista)}
        {--days=1 : Solo considerar archivos con más de N días para evitar borrar uploads en curso}';

    protected $description = 'Lista o borra archivos de storage/app/public no referenciados en BD (avatars, tiendas, productos)';

    /**
     * Carpetas que escaneamos. Para cada una decimos qué columnas de qué modelos
     * almacenan rutas a archivos dentro de esa carpeta.
     */
    private array $reglas = [
        'avatars'         => [User::class    => ['avatar']],
        'tiendas/logos'   => [Tienda::class  => ['logo']],
        'tiendas/portadas'=> [Tienda::class  => ['imagen_portada']],
        'productos'       => [Producto::class=> ['imagen']],
    ];

    public function handle(): int
    {
        $force      = (bool) $this->option('force');
        $minEdad    = (int)  $this->option('days');
        $disk       = Storage::disk('public');
        $totalSize  = 0;
        $totalFiles = 0;

        $this->info($force ? '🧹 Modo BORRADO REAL' : '🔍 Modo dry-run (usa --force para borrar)');
        $this->line("   Solo archivos con > {$minEdad} día(s) de antigüedad");
        $this->newLine();

        foreach ($this->reglas as $carpeta => $modelos) {
            if (!$disk->exists($carpeta)) {
                continue;
            }

            // Construir set de rutas referenciadas en BD para esta carpeta
            $referenciadas = collect();
            foreach ($modelos as $modelo => $columnas) {
                foreach ($columnas as $col) {
                    $referenciadas = $referenciadas->merge(
                        $modelo::query()
                            ->whereNotNull($col)
                            ->where($col, 'like', "{$carpeta}/%")
                            ->pluck($col)
                    );
                }
            }
            $referenciadas = $referenciadas->filter()->unique()->values()->all();

            // Listar archivos físicos
            $archivos = $disk->allFiles($carpeta);
            $huerfanos = [];
            $umbral = now()->subDays($minEdad)->getTimestamp();

            foreach ($archivos as $rutaArchivo) {
                if (in_array($rutaArchivo, $referenciadas, true)) continue;

                // Solo borrar si es lo bastante antiguo (evita pisar uploads en curso)
                $modificadoEn = $disk->lastModified($rutaArchivo);
                if ($modificadoEn > $umbral) continue;

                $huerfanos[] = $rutaArchivo;
            }

            if (empty($huerfanos)) {
                $this->line("✓ {$carpeta}: sin huérfanos");
                continue;
            }

            $tamCarpeta = 0;
            foreach ($huerfanos as $h) {
                $tamCarpeta += $disk->size($h);
            }
            $tamMB = round($tamCarpeta / 1024 / 1024, 2);

            $this->warn("⚠ {$carpeta}: " . count($huerfanos) . " huérfano(s) — {$tamMB} MB");

            // Mostrar primeros 5 ejemplos
            foreach (array_slice($huerfanos, 0, 5) as $h) {
                $this->line("    · {$h}");
            }
            if (count($huerfanos) > 5) {
                $this->line("    · ... y " . (count($huerfanos) - 5) . ' más');
            }

            if ($force) {
                foreach ($huerfanos as $h) {
                    $disk->delete($h);
                }
                $this->info("    ✓ Borrados.");
            }

            $totalSize  += $tamCarpeta;
            $totalFiles += count($huerfanos);
        }

        $this->newLine();
        $tamTotalMB = round($totalSize / 1024 / 1024, 2);

        if ($totalFiles === 0) {
            $this->info('🎉 Storage limpio, no hay archivos huérfanos.');
            return self::SUCCESS;
        }

        if ($force) {
            $this->info("✓ Liberados {$tamTotalMB} MB ({$totalFiles} archivos).");
        } else {
            $this->comment("→ Se liberarían {$tamTotalMB} MB ({$totalFiles} archivos). Re-ejecuta con --force para borrar.");
        }

        return self::SUCCESS;
    }
}
