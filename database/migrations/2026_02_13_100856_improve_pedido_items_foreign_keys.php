<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pedido_items', function (Blueprint $table) {
            // Eliminar las foreign keys actuales con cascade
            $table->dropForeign(['producto_id']);
            $table->dropForeign(['tienda_id']);
        });

        Schema::table('pedido_items', function (Blueprint $table) {
            // Hacer nullable los IDs para que puedan aceptar NULL
            $table->unsignedBigInteger('producto_id')->nullable()->change();
            $table->unsignedBigInteger('tienda_id')->nullable()->change();
            
            // Agregar campos para preservar información histórica
            $table->string('producto_nombre')->nullable()->after('producto_id');
            $table->string('tienda_nombre')->nullable()->after('tienda_id');
            $table->string('producto_imagen')->nullable()->after('producto_nombre');
            
            // Recrear foreign keys sin cascade (nullear en vez de borrar)
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('set null');
            $table->foreign('tienda_id')->references('id')->on('tiendas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido_items', function (Blueprint $table) {
            // Eliminar las nuevas foreign keys
            $table->dropForeign(['producto_id']);
            $table->dropForeign(['tienda_id']);
            
            // Eliminar los campos de snapshot
            $table->dropColumn(['producto_nombre', 'tienda_nombre', 'producto_imagen']);
            
            // Restaurar foreign keys originales con cascade
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
            $table->foreign('tienda_id')->references('id')->on('tiendas')->onDelete('cascade');
        });
    }
};
