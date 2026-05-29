<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    protected $table = 'sale_details';

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'price',
        'discount',
    ];
    public function product(){
        return $this->belongsTo(Product::class);
    }
}
