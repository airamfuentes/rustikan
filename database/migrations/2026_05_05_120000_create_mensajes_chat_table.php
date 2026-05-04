<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensajes_chat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('remitente_id')->constrained('users')->cascadeOnDelete();
            $table->text('mensaje');
            $table->boolean('leido_admin')->default(false);
            $table->boolean('leido_supplier')->default(false);
            $table->timestamps();

            $table->index(['supplier_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes_chat');
    }
};
