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

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /** ¿El producto se vende por variantes? */
    public function getHasVariantsAttribute(): bool
    {
        return $this->variants()->exists();
    }

    /** Stock efectivo: suma de variantes si las hay, si no el stock propio. */
    public function getEffectiveStockAttribute(): int
    {
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            return (int) $this->variants->sum('stock');
        }
        return $this->variants()->exists() ? (int) $this->variants()->sum('stock') : (int) $this->stock;
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

    /** Subtotal del ítem según cantidad, aplicando la promo vigente (combo/mayorista/%/$). */
    public function promoSubtotal(int $qty, ?float $unitPrice = null): float
    {
        $unit = $unitPrice ?? (float) $this->sell_price;
        $promo = $this->activePromotions()->first();
        return $promo ? $promo->subtotalFor($unit, $qty) : round($qty * $unit, 2);
    }

    /** Etiqueta de la promo vigente (para la tienda), o null. */
    public function getPromoLabelAttribute(): ?string
    {
        if (! $this->relationLoaded('promotions')) {
            return null;
        }
        return $this->activePromotions()->first()?->label;
    }
}
