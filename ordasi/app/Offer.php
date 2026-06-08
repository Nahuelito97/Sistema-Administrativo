<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Offer extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'banner', 'discount_percent', 'starts_at', 'ends_at', 'is_active'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $appends = ['banner_url'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'offer_product');
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->banner ? Storage::disk('public')->url($this->banner) : null;
    }

    public function isRunning(): bool
    {
        $now = now();
        return $this->is_active && $this->starts_at <= $now && $this->ends_at >= $now;
    }

    public function scopeRunning(Builder $query): Builder
    {
        $now = now();
        return $query->where('is_active', true)->where('starts_at', '<=', $now)->where('ends_at', '>=', $now);
    }
}
