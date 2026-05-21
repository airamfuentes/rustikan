<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DemoFresh extends Command
{
    protected $signature   = 'demo:fresh';
    protected $description = 'Limpia tablas transaccionales (pedidos, reseñas, notificaciones, etc.)';

    public function handle(): int
    {
        if (!$this->confirm('¿Limpiar pedidos, albaranes, reseñas, notificaciones y datos transaccionales?', true)) {
            $this->info('Cancelado.');
            return self::SUCCESS;
        }

        $this->info('Limpiando tablas transaccionales…');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = [
            'pedido_items',
            'pedidos',
            'entradas_mercancia',
            'resenas',
            'notificaciones',
            'mensajes_chat',
            'rusticoin_transactions',
            'stock_alerts',
            'tienda_favoritas',
            'activity_logs',
            'solicitudes_cambio',
            'solicitudes_creacion_tienda',
            'solicitudes_empleo',
            'sessions',
            'jobs',
            'failed_jobs',
            'cache',
            'cache_locks',
        ];

        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
                $this->line("  ✓ {$table}");
            }
        }

        // Reset rusticoin balance on all users
        DB::table('users')->update(['rusticoin_balance' => 0]);
        $this->line('  ✓ rusticoin_balance → 0 en todos los usuarios');

        // Reset tienda counters
        DB::table('tiendas')->update(['total_resenas' => 0, 'valoracion' => 0]);
        $this->line('  ✓ valoración y total_reseñas reseteados en tiendas');

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Limpieza completada.');

        $this->info('¡Limpieza completada!');
        return self::SUCCESS;
    }
}
