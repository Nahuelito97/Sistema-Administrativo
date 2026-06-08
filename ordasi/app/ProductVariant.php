<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'name', 'attributes', 'sku', 'price', 'stock'];

    protected $casts = [
        'attributes' => 'array',
        'price'      => 'decimal:2',
        'stock'      => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
