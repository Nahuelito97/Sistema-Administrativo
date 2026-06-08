<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    /** Estados del vendedor (state machine simple). */
    public const SELLER_INACTIVE = 1; // nunca pidió vender
    public const SELLER_PENDING  = 2; // solicitó, espera aprobación admin
    public const SELLER_ACTIVE   = 3; // aprobado, puede vender
    public const SELLER_BANNED   = 4; // suspendido

    public const SELLER_LABELS = [
        self::SELLER_INACTIVE => 'inactive',
        self::SELLER_PENDING  => 'pending',
        self::SELLER_ACTIVE   => 'active',
        self::SELLER_BANNED   => 'banned',
    ];

    /** Transiciones válidas del estado de vendedor. */
    private const SELLER_TRANSITIONS = [
        self::SELLER_INACTIVE => [self::SELLER_PENDING, self::SELLER_ACTIVE],
        self::SELLER_PENDING  => [self::SELLER_ACTIVE, self::SELLER_BANNED, self::SELLER_INACTIVE],
        self::SELLER_ACTIVE   => [self::SELLER_BANNED],
        self::SELLER_BANNED   => [self::SELLER_ACTIVE],
    ];

    /**
     * Fija el guard de spatie a 'web' (los permisos/roles se sembraron así).
     * Evita el mismatch cuando la request autentica con el guard 'sanctum'.
     */
    protected string $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'company_id',
        'status_seller_id', 'dni', 'dni_front', 'dni_back', 'selfie', 'cbu', 'seller_since',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password', 'remember_token',
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
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /** Label legible del estado de vendedor. */
    public function getSellerStatusAttribute(): string
    {
        return self::SELLER_LABELS[$this->status_seller_id ?? self::SELLER_INACTIVE] ?? 'inactive';
    }

    public function isSeller(): bool
    {
        return (int) $this->status_seller_id === self::SELLER_ACTIVE;
    }

    /** ¿Puede vender? Aprobado + con tienda. */
    public function canSell(): bool
    {
        return $this->isSeller() && $this->company_id !== null;
    }

    public function canTransitionSellerTo(int $status): bool
    {
        $current = (int) ($this->status_seller_id ?? self::SELLER_INACTIVE);
        return in_array($status, self::SELLER_TRANSITIONS[$current] ?? [], true);
    }

    /** Aplica una transición de estado de vendedor si es válida. */
    public function transitionSellerTo(int $status): bool
    {
        if (! $this->canTransitionSellerTo($status)) {
            return false;
        }
        $this->status_seller_id = $status;
        if ($status === self::SELLER_ACTIVE && ! $this->seller_since) {
            $this->seller_since = now()->toDateString();
        }
        $this->save();
        return true;
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function cart()
    {
        return $this->hasOne(ShoppingCart::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}
