<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Añadir columna pedido_id + FK (solo si aún no existe).
        if (!Schema::hasColumn('resenas', 'pedido_id')) {
            Schema::table('resenas', function (Blueprint $table) {
                $table->foreignId('pedido_id')
                    ->nullable()
                    ->after('tienda_id')
                    ->constrained('pedidos')
                    ->nullOnDelete();
            });
        }

        // 2) Soltar las foreign keys que cuelgan del unique antiguo
        //    (MySQL no permite eliminar un índice usado por una FK).
        Schema::table('resenas', function (Blueprint $table) {
            try { $table->dropForeign(['user_id']); }   catch (\Throwable) {}
            try { $table->dropForeign(['tienda_id']); } catch (\Throwable) {}
        });

        // 3) Eliminar el unique antiguo (user_id, tienda_id), si sigue existiendo.
        if ($this->indexExists('resenas', 'resenas_user_id_tienda_id_unique')) {
            Schema::table('resenas', function (Blueprint $table) {
                $table->dropUnique('resenas_user_id_tienda_id_unique');
            });
        }

        // 4) Recrear las foreign keys (solo si no existen ya).
        Schema::table('resenas', function (Blueprint $table) {
            try { $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete(); }
            catch (\Throwable) {}
            try { $table->foreign('tienda_id')->references('id')->on('tiendas')->cascadeOnDelete(); }
            catch (\Throwable) {}
        });

        // 5) Añadir unique sobre pedido_id (NULLs no colisionan en MySQL).
        if (!$this->indexExists('resenas', 'resenas_pedido_id_unique')) {
            Schema::table('resenas', function (Blueprint $table) {
                $table->unique('pedido_id');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('resenas', 'resenas_pedido_id_unique')) {
            Schema::table('resenas', function (Blueprint $table) {
                $table->dropUnique(['pedido_id']);
            });
        }

        if (Schema::hasColumn('resenas', 'pedido_id')) {
            Schema::table('resenas', function (Blueprint $table) {
                try { $table->dropConstrainedForeignId('pedido_id'); } catch (\Throwable) {}
            });
        }

        // Restaurar el unique original sin colisionar con las FKs existentes.
        Schema::table('resenas', function (Blueprint $table) {
            try { $table->dropForeign(['user_id']); }   catch (\Throwable) {}
            try { $table->dropForeign(['tienda_id']); } catch (\Throwable) {}
        });

        if (!$this->indexExists('resenas', 'resenas_user_id_tienda_id_unique')) {
            Schema::table('resenas', function (Blueprint $table) {
                $table->unique(['user_id', 'tienda_id']);
            });
        }

        Schema::table('resenas', function (Blueprint $table) {
            try { $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete(); }
            catch (\Throwable) {}
            try { $table->foreign('tienda_id')->references('id')->on('tiendas')->cascadeOnDelete(); }
            catch (\Throwable) {}
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = DB::select(
            'SELECT COUNT(*) AS c FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $index]
        );
        return ((int) ($rows[0]->c ?? 0)) > 0;
    }
};
