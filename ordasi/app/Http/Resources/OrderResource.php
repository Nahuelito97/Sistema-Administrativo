<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'user_id'         => $this->user_id,
            'company_id'      => $this->company_id,
            'seller_id'       => $this->seller_id,
            'order_date'      => $this->order_date,
            'tax'             => $this->tax,
            'total'           => $this->total,
            'shipping_status' => $this->shipping_status,
            'payment_status'  => $this->payment_status,
            'shipping_address'=> $this->shipping_address,
            'user'            => new UserResource($this->whenLoaded('user')),
            'company'         => new CompanyResource($this->whenLoaded('company')),
            'details'         => OrderDetailResource::collection($this->whenLoaded('details')),
            'created_at'      => $this->created_at,
        ];
    }
}
