<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'product_id'   => $this->product_id,
            'variant_name' => $this->variant_name,
            'quantity'     => $this->quantity,
            'price'        => $this->price,
            'subtotal'     => round($this->quantity * (float) $this->price, 2),
            'product'      => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
