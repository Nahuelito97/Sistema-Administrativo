<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'name', 'promotion_type', 'start_date', 'ending_date', 'discount_rate', 'fixed_amount_discount',
        'combo_buy', 'combo_pay', 'wholesale_min_qty', 'wholesale_price',
    ];

    protected $casts = ['start_date' => 'datetime', 'ending_date' => 'datetime'];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function isActive(): bool
    {
        $now = now();
        return $this->start_date <= $now && $this->ending_date >= $now;
    }

    /** Subtotal del ítem (precio unitario × cantidad) aplicando esta promo. */
    public function subtotalFor(float $unitPrice, int $qty): float
    {
        return round(match ($this->promotion_type) {
            'percent'   => $qty * $unitPrice * (1 - (float) $this->discount_rate / 100),
            'fixed_amount' => $qty * max(0, $unitPrice - (float) $this->fixed_amount_discount),
            'combo'     => $this->comboPayUnits($qty) * $unitPrice,
            'wholesale' => $qty >= (int) $this->wholesale_min_qty ? $qty * (float) $this->wholesale_price : $qty * $unitPrice,
            default     => $qty * $unitPrice,
        }, 2);
    }

    /** Unidades a pagar en un combo NxM (ej. 2x1: cada 2 pagás 1). */
    private function comboPayUnits(int $qty): int
    {
        $buy = max(1, (int) $this->combo_buy);
        $pay = (int) $this->combo_pay;
        return intdiv($qty, $buy) * $pay + ($qty % $buy);
    }

    /** Etiqueta corta para mostrar en la tienda. */
    public function getLabelAttribute(): string
    {
        return match ($this->promotion_type) {
            'percent'   => '-' . rtrim(rtrim((string) $this->discount_rate, '0'), '.') . '%',
            'fixed_amount' => '-$' . rtrim(rtrim((string) $this->fixed_amount_discount, '0'), '.'),
            'combo'     => "{$this->combo_buy}x{$this->combo_pay}",
            'wholesale' => "Desde {$this->wholesale_min_qty}u",
            default     => 'Oferta',
        };
    }
}
