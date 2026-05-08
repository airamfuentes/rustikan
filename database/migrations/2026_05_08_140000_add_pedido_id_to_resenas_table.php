<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('resenas', function (Blueprint $table) {
            // Una reseña pertenece a un pedido. Nullable para registros antiguos.
            // Unique para garantizar que un pedido solo puede tener una reseña.
            // (NULLs no chocan en MySQL con índices unique).
            $table->foreignId('pedido_id')
                ->nullable()
                ->after('tienda_id')
                ->constrained('pedidos')
                ->nullOnDelete();
        });

        // Eliminar el unique antiguo (user_id, tienda_id) — ahora un usuario
        // puede tener varias reseñas en una misma tienda (una por pedido).
        Schema::table('resenas', function (Blueprint $table) {
            try {
                $table->dropUnique('resenas_user_id_tienda_id_unique');
            } catch (\Throwable) {
                // Índice ya no existe en algunos entornos
            }
        });

        // Añadir unique sobre pedido_id (NULLs no colisionan).
        Schema::table('resenas', function (Blueprint $table) {
            $table->unique('pedido_id');
        });
    }

    public function down(): void
    {
        Schema::table('resenas', function (Blueprint $table) {
            try { $table->dropUnique(['pedido_id']); } catch (\Throwable) {}
            try { $table->dropConstrainedForeignId('pedido_id'); } catch (\Throwable) {}
            $table->unique(['user_id', 'tienda_id']);
        });
    }
};
