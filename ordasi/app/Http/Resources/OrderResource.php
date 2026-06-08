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
            'can_rate'        => $this->shipping_status === 'DELIVERED' && ! $this->rating()->exists(),
            'shipping_address'=> $this->shipping_address,
            'shipping_carrier'=> $this->shipping_carrier,
            'tracking_code'   => $this->tracking_code,
            'shipped_at'      => $this->shipped_at,
            'delivered_at'    => $this->delivered_at,
            'user'            => new UserResource($this->whenLoaded('user')),
            'company'         => new CompanyResource($this->whenLoaded('company')),
            'details'         => OrderDetailResource::collection($this->whenLoaded('details')),
            'created_at'      => $this->created_at,
        ];
    }
}
