<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('solicitudes_empleo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellidos', 100);
            $table->string('email', 180);
            $table->string('telefono', 20)->nullable();
            $table->string('puesto', 120);                 // ej: "Desarrollador", "Atención al cliente"
            $table->text('mensaje');                        // carta de presentación
            $table->string('cv_path', 500)->nullable();    // ruta en storage/cvs
            $table->string('cv_nombre_original', 200)->nullable();
            $table->string('estado', 20)->default('pendiente'); // pendiente, revisada, contactada, rechazada
            $table->timestamps();

            $table->index('estado');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_empleo');
    }
};
