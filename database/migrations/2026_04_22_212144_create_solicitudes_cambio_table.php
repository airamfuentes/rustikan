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
        Schema::create('solicitudes_cambio', function (Blueprint $table) {
            $table->id();

            // Quién pide el cambio y a qué entidad pertenece
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tienda_id')->constrained()->cascadeOnDelete();

            // Si el cambio es sobre un producto en concreto (null → es sobre la tienda)
            $table->foreignId('producto_id')->nullable()->constrained()->nullOnDelete();

            // Tipo de operación: 'update_tienda' | 'create_producto' | 'update_producto' | 'delete_producto'
            $table->string('tipo', 30);

            // Campo específico que se quiere cambiar (ej: 'nombre', 'precio', 'imagen')
            // Null cuando tipo=create_producto (el valor_nuevo contiene todo el objeto)
            $table->string('campo', 60)->nullable();

            // Valores serializados a JSON para soporte de cualquier tipo de dato (imágenes, números, etc.)
            $table->json('valor_anterior')->nullable();
            $table->json('valor_nuevo')->nullable();

            // Estado del flujo de aprobación
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');

            // Motivo de rechazo (opcional, lo escribe el admin)
            $table->text('motivo_rechazo')->nullable();

            // Quién revisó y cuándo
            $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_at')->nullable();

            $table->timestamps();

            // Índices de consulta frecuente
            $table->index(['tienda_id', 'estado']);
            $table->index(['estado', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_cambio');
    }
};
