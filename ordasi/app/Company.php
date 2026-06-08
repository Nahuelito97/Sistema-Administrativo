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

    /** Reputación de la tienda: promedio y cantidad de reseñas de sus productos. */
    public function reputation(): array
    {
        $row = Rating::where('rateable_type', Product::class)
            ->whereIn('rateable_id', $this->products()->pluck('id'))
            ->selectRaw('AVG(rating) as avg, COUNT(*) as cnt')
            ->first();

        return [
            'avg'   => $row && $row->cnt ? round((float) $row->avg, 1) : null,
            'count' => (int) ($row->cnt ?? 0),
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
