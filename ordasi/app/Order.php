<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'company_id', 'seller_id', 'order_date', 'tax', 'total',
        'shipping_status', 'payment_status', 'shipping_address',
        'payment_platform', 'preference_id', 'payment_id',
    ];

    protected $casts = ['order_date' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }
}
