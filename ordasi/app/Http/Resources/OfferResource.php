<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'slug'             => $this->slug,
            'description'      => $this->description,
            'banner_url'       => $this->banner_url,
            'discount_percent' => $this->discount_percent,
            'starts_at'        => $this->starts_at,
            'ends_at'          => $this->ends_at,
            'is_active'        => $this->is_active,
            'is_running'       => $this->isRunning(),
            'products_count'   => $this->whenCounted('products'),
            'products'         => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
