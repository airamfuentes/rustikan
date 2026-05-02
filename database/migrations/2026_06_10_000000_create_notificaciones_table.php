<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tipo', 50);          // ej: nuevo_pedido, solicitud_aprobada, etc.
            $table->string('titulo', 255);
            $table->text('mensaje');
            $table->string('enlace', 500)->nullable();
            $table->string('icono', 50)->default('bell');  // bell, check, x, shopping-cart...
            $table->string('color', 20)->default('primary'); // primary, green, red, orange, blue
            $table->boolean('leida')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'leida']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
