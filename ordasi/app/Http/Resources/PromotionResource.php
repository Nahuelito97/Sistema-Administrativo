<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'promotion_type'        => $this->promotion_type,
            'start_date'            => $this->start_date,
            'ending_date'           => $this->ending_date,
            'discount_rate'         => $this->discount_rate,
            'fixed_amount_discount' => $this->fixed_amount_discount,
            'is_active'             => $this->isActive(),
            'products_count'        => $this->whenCounted('products'),
            'products'              => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
