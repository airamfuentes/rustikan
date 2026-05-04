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
        'fecha_nacimiento',
        'email_verification_code',
        'email_verification_expires_at',
        'rusticoin_balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'             => 'datetime',
            'email_verification_expires_at' => 'datetime',
            'fecha_nacimiento'              => 'date',
            'password'                      => 'hashed',
            'rusticoin_balance'             => 'decimal:2',
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

    public function isSupplier(): bool
    {
        return $this->role === 'supplier';
    }

    /**
     * Owners and admins are verified implicitly: their role is assigned by an admin,
     * so email verification is not required for them.
     */
    public function hasVerifiedEmail(): bool
    {
        if ($this->role === 'owner' || $this->role === 'admin') {
            return true;
        }

        return parent::hasVerifiedEmail();
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
     * Get RustiCoin transactions
     */
    public function rusticoinTransactions()
    {
        return $this->hasMany(RusticoinTransaction::class);
    }

    /**
     * Send the email verification notification using a 6-digit code.
     */
    public function sendEmailVerificationNotification(): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->forceFill([
            'email_verification_code'         => $code,
            'email_verification_expires_at'   => now()->addMinutes(5),
        ])->save();

        $this->notify(new VerificacionEmail($code));
    }

    /**
     * Send the password reset notification (custom branded template).
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordCustom($token));
    }
}
