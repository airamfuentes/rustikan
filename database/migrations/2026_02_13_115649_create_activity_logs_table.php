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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); // nuevo_usuario, nuevo_pedido, nueva_tienda, eliminar_tienda, etc.
            $table->text('descripcion'); // Descripción legible de la actividad
            $table->string('icono')->default('editar'); // Clave semántica que el frontend mapea a un icono Lucide
            $table->string('color')->default('gray'); // Color del tema (blue, green, red, etc)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Usuario que realizó la acción
            $table->string('model_type')->nullable(); // Tipo de modelo afectado (App\Models\Tienda)
            $table->unsignedBigInteger('model_id')->nullable(); // ID del modelo afectado
            $table->json('datos')->nullable(); // Datos adicionales en JSON
            $table->timestamps();
            
            $table->index(['tipo', 'created_at']);
            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
