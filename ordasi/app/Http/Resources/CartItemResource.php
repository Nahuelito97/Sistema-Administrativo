<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // El precio sale de la variante si la hay, si no del producto.
        $price = (float) ($this->variant->price ?? $this->product->sell_price ?? 0);
        return [
            'id'                 => $this->id,
            'product_id'         => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'variant_name'       => $this->variant?->name,
            'unit_price'         => round($price, 2),
            'quantity'           => $this->quantity,
            'subtotal'           => round($this->quantity * $price, 2),
            'product'            => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
