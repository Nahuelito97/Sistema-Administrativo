<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClaimResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'order_id'    => $this->order_id,
            'type'        => $this->type,
            'reason'      => $this->reason,
            'status'      => $this->status,
            'resolution'  => $this->resolution,
            'resolved_at' => $this->resolved_at,
            'company'     => new CompanyResource($this->whenLoaded('company')),
            'user'        => $this->whenLoaded('user', fn () => ['id' => $this->user?->id, 'name' => $this->user?->name]),
            'order'       => new OrderResource($this->whenLoaded('order')),
            'created_at'  => $this->created_at,
        ];
    }
}
