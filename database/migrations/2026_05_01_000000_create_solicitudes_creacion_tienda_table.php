<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_creacion_tienda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nombre_tienda');
            $table->string('nombre_contacto');
            $table->string('email');
            $table->string('telefono', 20)->nullable();
            $table->string('categoria');
            $table->text('descripcion');
            $table->string('municipio')->nullable();
            $table->string('direccion')->nullable();
            $table->string('web')->nullable();
            $table->string('instagram')->nullable();
            $table->text('productos_descripcion');
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->text('notas_admin')->nullable();
            $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_at')->nullable();
            $table->timestamps();

            $table->index(['estado', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_creacion_tienda');
    }
};
