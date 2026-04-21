<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resenas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tienda_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('puntuacion'); // 1–5
            $table->string('titulo', 120)->nullable();
            $table->text('comentario');
            $table->timestamps();

            // Un usuario solo puede reseñar una tienda una vez
            $table->unique(['user_id', 'tienda_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resenas');
    }
};
