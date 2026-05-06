<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Añadir saldo RustiCoin a users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'rusticoin_balance')) {
                $table->decimal('rusticoin_balance', 10, 2)->default(0)->after('email_verified_at');
            }
        });

        // Añadir método de pago a pedidos
        Schema::table('pedidos', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos', 'metodo_pago')) {
                $table->string('metodo_pago', 20)->default('tarjeta')->after('notas');
            }
        });

        // Tabla de transacciones del monedero
        if (!Schema::hasTable('rusticoin_transactions')) {
            Schema::create('rusticoin_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->enum('tipo', ['recarga', 'compra', 'reembolso', 'retiro']);
                $table->decimal('cantidad', 10, 2);
                $table->decimal('saldo_despues', 10, 2);
                $table->string('descripcion')->nullable();
                $table->foreignId('pedido_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rusticoin_transactions');
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('metodo_pago');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rusticoin_balance');
        });
    }
};
