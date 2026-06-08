<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

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

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class);
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /** Promociones vigentes (entre start y ending date). */
    public function activePromotions()
    {
        $now = now();
        return $this->promotions->filter(fn ($p) => $p->start_date <= $now && $p->ending_date >= $now);
    }

    /** Precio final aplicando las promociones vigentes. */
    public function getDiscountedPriceAttribute(): float
    {
        $price = (float) $this->sell_price;
        foreach ($this->activePromotions() as $promotion) {
            if ($promotion->promotion_type === 'percent') {
                $price -= $price * ((float) $promotion->discount_rate / 100);
            } else {
                $price -= (float) $promotion->fixed_amount_discount;
            }
        }
        return round(max($price, 0), 2);
    }

    public function getHasPromotionAttribute(): bool
    {
        return $this->relationLoaded('promotions') && $this->activePromotions()->isNotEmpty();
    }
}
