<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Añadir columna pedido_id con su foreign key.
        Schema::table('resenas', function (Blueprint $table) {
            $table->foreignId('pedido_id')
                ->nullable()
                ->after('tienda_id')
                ->constrained('pedidos')
                ->nullOnDelete();
        });

        // 2) Soltar las foreign keys que cuelgan del unique antiguo
        //    (MySQL no permite eliminar un índice usado por una FK).
        Schema::table('resenas', function (Blueprint $table) {
            try { $table->dropForeign(['user_id']); }   catch (\Throwable) {}
            try { $table->dropForeign(['tienda_id']); } catch (\Throwable) {}
        });

        // 3) Eliminar el unique antiguo (user_id, tienda_id).
        Schema::table('resenas', function (Blueprint $table) {
            try { $table->dropUnique('resenas_user_id_tienda_id_unique'); } catch (\Throwable) {}
        });

        // 4) Recrear las foreign keys.
        Schema::table('resenas', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('tienda_id')->references('id')->on('tiendas')->cascadeOnDelete();
        });

        // 5) Añadir unique sobre pedido_id (NULLs no colisionan en MySQL).
        Schema::table('resenas', function (Blueprint $table) {
            $table->unique('pedido_id');
        });
    }

    public function down(): void
    {
        Schema::table('resenas', function (Blueprint $table) {
            try { $table->dropUnique(['pedido_id']); } catch (\Throwable) {}
            try { $table->dropConstrainedForeignId('pedido_id'); } catch (\Throwable) {}
        });

        // Restaurar el unique original sin colisionar con las FKs existentes.
        Schema::table('resenas', function (Blueprint $table) {
            try { $table->dropForeign(['user_id']); }   catch (\Throwable) {}
            try { $table->dropForeign(['tienda_id']); } catch (\Throwable) {}
        });

        Schema::table('resenas', function (Blueprint $table) {
            $table->unique(['user_id', 'tienda_id']);
        });

        Schema::table('resenas', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('tienda_id')->references('id')->on('tiendas')->cascadeOnDelete();
        });
    }
};
