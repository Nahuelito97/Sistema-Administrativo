<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderRating extends Model
{
    protected $fillable = ['order_id', 'user_id', 'company_id', 'product_score', 'attention_score', 'shipping_score', 'comment'];

    /** Promedio de las 3 dimensiones. */
    public function getAverageAttribute(): float
    {
        return round(($this->product_score + $this->attention_score + $this->shipping_score) / 3, 1);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
