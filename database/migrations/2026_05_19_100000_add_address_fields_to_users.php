<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('calle', 100)->nullable()->after('direccion');
            $table->string('numero', 10)->nullable()->after('calle');
            $table->string('puerta', 20)->nullable()->after('numero');
            $table->string('cp', 5)->nullable()->after('puerta');
            $table->string('localidad', 100)->nullable()->after('cp');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['calle', 'numero', 'puerta', 'cp', 'localidad']);
        });
    }
};
