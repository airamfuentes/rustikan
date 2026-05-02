<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds 'supplier' to users.role enum and new pedido states:
     *   en_preparacion, enviado, incidencia
     */
    public function up(): void
    {
        // Modify users.role enum to include 'supplier'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'owner', 'admin', 'supplier') NOT NULL DEFAULT 'user'");

        // Modify pedidos.estado enum to include new states
        DB::statement("ALTER TABLE pedidos MODIFY COLUMN estado ENUM('pendiente', 'en_preparacion', 'confirmado', 'enviado', 'entregado', 'cancelado', 'incidencia', 'preparando', 'en_camino') NOT NULL DEFAULT 'pendiente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'owner', 'admin') NOT NULL DEFAULT 'user'");
        DB::statement("ALTER TABLE pedidos MODIFY COLUMN estado ENUM('pendiente', 'confirmado', 'preparando', 'en_camino', 'entregado', 'cancelado') NOT NULL DEFAULT 'pendiente'");
    }
};
