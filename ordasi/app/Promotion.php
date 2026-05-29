<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = ['name', 'promotion_type', 'start_date', 'ending_date', 'discount_rate', 'fixed_amount_discount'];

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
}
