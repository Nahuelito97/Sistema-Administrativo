<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $details = $this->details;
        return [
            'id'    => $this->id,
            'items' => CartItemResource::collection($details),
            'count' => $details->sum('quantity'),
            'total' => round($details->sum(fn ($d) => $d->quantity * (float) ($d->product->sell_price ?? 0)), 2),
        ];
    }
}
