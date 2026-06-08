<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'cuit',
        'cond_iva',
        'email',
        'phone',
        'address',
        'logo',
        'banner',
        'social_network',
        'minimum_order_amount',
        'status',
    ];

    protected $appends = ['logo_url', 'banner_url'];

    /**
     * Resolución por slug en rutas públicas de tienda (perfil /tienda/{slug}).
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function sellers()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /** URL pública del logo (o null si no tiene). */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }

    /** URL pública del banner (o null si no tiene). */
    public function getBannerUrlAttribute(): ?string
    {
        return $this->banner ? Storage::disk('public')->url($this->banner) : null;
    }

    /**
     * Reputación de la tienda: promedio de las calificaciones por venta
     * (producto/atención/envío). Si no hay, cae a las reseñas de productos.
     */
    public function reputation(): array
    {
        $row = OrderRating::where('company_id', $this->id)
            ->selectRaw('AVG((product_score + attention_score + shipping_score) / 3) as avg, COUNT(*) as cnt')
            ->first();

        if ($row && $row->cnt) {
            return ['avg' => round((float) $row->avg, 1), 'count' => (int) $row->cnt];
        }

        // Fallback: reseñas de productos.
        $prod = Rating::where('rateable_type', Product::class)
            ->whereIn('rateable_id', $this->products()->pluck('id'))
            ->selectRaw('AVG(rating) as avg, COUNT(*) as cnt')
            ->first();

        return [
            'avg'   => $prod && $prod->cnt ? round((float) $prod->avg, 1) : null,
            'count' => (int) ($prod->cnt ?? 0),
        ];
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $term
            ? $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('cuit', 'like', "%{$term}%"))
            : $query;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
