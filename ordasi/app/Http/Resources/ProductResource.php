<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'code'        => $this->code,
            'name'        => $this->name,
            'stock'       => $this->stock,
            'image'       => $this->image,
            'sell_price'  => $this->sell_price,
            'status'      => $this->status,
            'category_id' => $this->category_id,
            'provider_id' => $this->provider_id,
            'category'    => new CategoryResource($this->whenLoaded('category')),
            'provider'    => new ProviderResource($this->whenLoaded('provider')),
            'created_at'  => $this->created_at,
        ];
    }
}
