<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DemoFresh extends Command
{
    protected $signature   = 'demo:fresh {--seed : Re-seed demo data after cleaning}';
    protected $description = 'Limpia tablas transaccionales y opcionalmente re-seedea datos demo';

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

        if ($this->option('seed')) {
            $this->info('Re-seeding datos demo…');
            $this->call('db:seed', ['--class' => 'DemoUsersSeeder', '--force' => true]);
            $this->call('db:seed', ['--class' => 'DemoTiendasSeeder', '--force' => true]);
            $this->call('db:seed', ['--class' => 'DemoProductosSeeder', '--force' => true]);
            $this->call('db:seed', ['--class' => 'DemoResenasSeeder', '--force' => true]);
        }

        $this->info('¡Listo para el vídeo! 🎬');
        return self::SUCCESS;
    }
}
