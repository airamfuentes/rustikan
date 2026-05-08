<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tienda_favoritas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tienda_id')->constrained('tiendas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'tienda_id']);
            $table->index('tienda_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tienda_favoritas');
    }
};
