<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('apellidos')->nullable()->after('name');
            $table->unsignedTinyInteger('edad')->nullable()->after('direccion');
            $table->timestamp('telefono_verificado_at')->nullable()->after('email_verified_at');
            $table->string('sms_verification_token')->nullable()->after('telefono_verificado_at');
            $table->timestamp('sms_verification_expires_at')->nullable()->after('sms_verification_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'apellidos',
                'edad',
                'telefono_verificado_at',
                'sms_verification_token',
                'sms_verification_expires_at',
            ]);
        });
    }
};
