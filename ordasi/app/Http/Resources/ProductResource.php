<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'code'              => $this->code,
            'name'              => $this->name,
            'slug'              => $this->slug,
            'stock'             => $this->stock,
            'image'             => $this->image,
            'short_description' => $this->short_description,
            'long_description'  => $this->long_description,
            'sell_price'        => $this->sell_price,
            'status'            => $this->status,
            'visibility'        => $this->visibility,
            'views'             => $this->views,
            'category_id'       => $this->category_id,
            'subcategory_id'    => $this->subcategory_id,
            'provider_id'       => $this->provider_id,
            'brand_id'          => $this->brand_id,
            'company_id'        => $this->company_id,
            'company'           => new CompanyResource($this->whenLoaded('company')),
            'category'          => new CategoryResource($this->whenLoaded('category')),
            'subcategory'       => new SubcategoryResource($this->whenLoaded('subcategory')),
            'provider'          => new ProviderResource($this->whenLoaded('provider')),
            'brand'             => new BrandResource($this->whenLoaded('brand')),
            'images'            => ImageResource::collection($this->whenLoaded('images')),
            'variants'          => VariantResource::collection($this->whenLoaded('variants')),
            'has_variants'      => $this->when($this->relationLoaded('variants'), fn () => $this->variants->isNotEmpty()),
            'caracteristics'    => $this->whenLoaded('caracteristics', fn () => $this->caracteristics->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'value' => $c->pivot->value])),
            'promotions'        => PromotionResource::collection($this->whenLoaded('promotions')),
            'discounted_price'  => $this->when($this->relationLoaded('promotions'), fn () => $this->discounted_price),
            'has_promotion'     => $this->has_promotion,
            'promo_label'       => $this->when($this->relationLoaded('promotions'), fn () => $this->promo_label),
            'average_rating'    => $this->when($this->relationLoaded('ratings'), fn () => round((float) $this->ratings->avg('rating'), 1)),
            'ratings_count'     => $this->when($this->relationLoaded('ratings'), fn () => $this->ratings->count()),
            'created_at'        => $this->created_at,
        ];
    }
}
