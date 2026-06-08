<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'product_id' => $this->product_id,
            'name'       => $this->name,
            'attributes' => $this->attributes,
            'sku'        => $this->sku,
            'price'      => $this->price,
            'stock'      => $this->stock,
        ];
    }
}
