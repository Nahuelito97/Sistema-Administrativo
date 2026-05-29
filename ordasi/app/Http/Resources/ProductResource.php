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
            'category'          => new CategoryResource($this->whenLoaded('category')),
            'subcategory'       => new SubcategoryResource($this->whenLoaded('subcategory')),
            'provider'          => new ProviderResource($this->whenLoaded('provider')),
            'brand'             => new BrandResource($this->whenLoaded('brand')),
            'images'            => ImageResource::collection($this->whenLoaded('images')),
            'created_at'        => $this->created_at,
        ];
    }
}
