<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entradas_mercancia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tienda_id')->constrained()->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->integer('stock_anterior');
            $table->integer('cantidad_entrada');
            $table->integer('stock_nuevo');
            $table->string('numero_documento')->nullable(); // albarán, factura, etc.
            $table->string('proveedor')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entradas_mercancia');
    }
};
