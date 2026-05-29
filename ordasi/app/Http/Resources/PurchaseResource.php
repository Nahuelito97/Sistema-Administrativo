<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'provider_id'  => $this->provider_id,
            'user_id'      => $this->user_id,
            'purchase_date'=> $this->purchase_date,
            'tax'          => $this->tax,
            'total'        => $this->total,
            'status'       => $this->status,
            'picture'      => $this->picture,
            'provider'     => new ProviderResource($this->whenLoaded('provider')),
            'user'         => new UserResource($this->whenLoaded('user')),
            'details'      => PurchaseDetailResource::collection($this->whenLoaded('purchaseDetails')),
            'created_at'   => $this->created_at,
        ];
    }
}
