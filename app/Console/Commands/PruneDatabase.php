<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Notificacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Limpia tablas que crecen indefinidamente y no aportan valor pasado un tiempo:
 *   - activity_logs:    > 90 días
 *   - notificaciones:   leídas > 30 días, no leídas > 180 días
 *   - mensajes_chat:    > 180 días
 *   - sessions:         las de Laravel ya tienen el cleanup automático según session.lifetime
 *
 * Uso:
 *   php artisan db:prune                    (dry-run)
 *   php artisan db:prune --force            (borra)
 *   php artisan db:prune --force --quiet    (cron-friendly)
 */
class PruneDatabase extends Command
{
    protected $signature = 'db:prune
        {--force : Borrar de verdad}
        {--days-logs=90 : Días para borrar activity_logs antiguos}
        {--days-notif-leidas=30 : Días para notificaciones leídas}
        {--days-notif-no-leidas=180 : Días para notificaciones no leídas}
        {--days-chat=180 : Días para mensajes de chat antiguos}';

    protected $description = 'Borra registros antiguos de tablas que crecen indefinidamente (logs, notificaciones, chats)';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $this->info($force ? '🧹 Modo BORRADO REAL' : '🔍 Modo dry-run (usa --force para borrar)');
        $this->newLine();

        $reglas = [
            [
                'titulo' => 'activity_logs antiguos',
                'days'   => (int) $this->option('days-logs'),
                'query'  => fn ($since) => ActivityLog::where('created_at', '<', $since),
            ],
            [
                'titulo' => 'notificaciones leídas',
                'days'   => (int) $this->option('days-notif-leidas'),
                'query'  => fn ($since) => Notificacion::where('leida', true)->where('created_at', '<', $since),
            ],
            [
                'titulo' => 'notificaciones NO leídas (muy antiguas)',
                'days'   => (int) $this->option('days-notif-no-leidas'),
                'query'  => fn ($since) => Notificacion::where('leida', false)->where('created_at', '<', $since),
            ],
            [
                'titulo' => 'mensajes_chat antiguos',
                'days'   => (int) $this->option('days-chat'),
                'query'  => function ($since) {
                    if (!class_exists(\App\Models\MensajeChat::class)) return null;
                    return \App\Models\MensajeChat::where('created_at', '<', $since);
                },
            ],
        ];

        $totalBorrado = 0;

        foreach ($reglas as $r) {
            $since = now()->subDays($r['days']);
            $query = ($r['query'])($since);
            if (!$query) {
                $this->line("· {$r['titulo']}: tabla no existe, se omite");
                continue;
            }

            $count = $query->count();
            if ($count === 0) {
                $this->line("✓ {$r['titulo']}: nada que borrar");
                continue;
            }

            $this->warn("⚠ {$r['titulo']}: {$count} fila(s) > {$r['days']} días");

            if ($force) {
                $borrados = $query->delete();
                $this->info("    ✓ Borradas {$borrados} fila(s)");
                $totalBorrado += $borrados;
            }
        }

        // Optimizar tablas tras el delete (recupera espacio en disco en MySQL)
        if ($force && $totalBorrado > 0) {
            $tablas = ['activity_logs', 'notificaciones', 'mensajes_chat'];
            foreach ($tablas as $t) {
                if (!Schema::hasTable($t)) continue;
                try {
                    DB::statement("OPTIMIZE TABLE `{$t}`");
                    $this->line("  · OPTIMIZE TABLE {$t}");
                } catch (\Throwable) {
                    // ignorar (algunas configs no soportan OPTIMIZE)
                }
            }
        }

        $this->newLine();
        if ($force) {
            $this->info("✓ Total borrado: {$totalBorrado} fila(s).");
        } else {
            $this->comment('→ Re-ejecuta con --force para borrar.');
        }

        return self::SUCCESS;
    }
}
