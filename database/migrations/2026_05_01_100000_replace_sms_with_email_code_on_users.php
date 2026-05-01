<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar columnas de verificación SMS
            $table->dropColumn([
                'telefono_verificado_at',
                'sms_verification_token',
                'sms_verification_expires_at',
            ]);

            // Añadir columnas para código de verificación de email
            $table->string('email_verification_code', 6)->nullable()->after('email_verified_at');
            $table->timestamp('email_verification_expires_at')->nullable()->after('email_verification_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_verification_code', 'email_verification_expires_at']);

            $table->timestamp('telefono_verificado_at')->nullable();
            $table->string('sms_verification_token')->nullable();
            $table->timestamp('sms_verification_expires_at')->nullable();
        });
    }
};
