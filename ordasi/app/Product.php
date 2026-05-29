<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'slug',
        'stock',
        'image',
        'short_description',
        'long_description',
        'sell_price',
        'status',
        'visibility',
        'category_id',
        'subcategory_id',
        'provider_id',
        'brand_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
