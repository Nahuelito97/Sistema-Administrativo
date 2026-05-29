<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'client_id' => $this->client_id,
            'user_id'   => $this->user_id,
            'sale_date' => $this->sale_date,
            'tax'       => $this->tax,
            'total'     => $this->total,
            'status'    => $this->status,
            'client'    => new ClientResource($this->whenLoaded('client')),
            'user'      => new UserResource($this->whenLoaded('user')),
            'details'   => SaleDetailResource::collection($this->whenLoaded('saleDetails')),
            'created_at'=> $this->created_at,
        ];
    }
}
