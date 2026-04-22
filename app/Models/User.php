<?php

namespace App\Models;

use App\Notifications\ResetPasswordCustom;
use App\Notifications\VerificacionEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'apellidos',
        'avatar',
        'email',
        'password',
        'role',
        'telefono',
        'direccion',
        'edad',
        'telefono_verificado_at',
        'sms_verification_token',
        'sms_verification_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'sms_verification_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'telefono_verificado_at' => 'datetime',
            'sms_verification_expires_at' => 'datetime',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Get the tiendas owned by the user
     */
    public function tiendas()
    {
        return $this->hasMany(Tienda::class);
    }

    /**
     * Get the pedidos placed by the user
     */
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    /**
     * Send the email verification notification (custom branded template).
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerificacionEmail());
    }

    /**
     * Send the password reset notification (custom branded template).
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordCustom($token));
    }
}
