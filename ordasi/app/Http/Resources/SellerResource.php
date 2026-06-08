<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SellerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'email'            => $this->email,
            'status_seller_id' => (int) $this->status_seller_id,
            'seller_status'    => $this->seller_status,
            'dni'              => $this->dni,
            'cbu'              => $this->cbu,
            'dni_front_url'    => $this->dni_front ? Storage::disk('public')->url($this->dni_front) : null,
            'dni_back_url'     => $this->dni_back ? Storage::disk('public')->url($this->dni_back) : null,
            'selfie_url'       => $this->selfie ? Storage::disk('public')->url($this->selfie) : null,
            'seller_since'     => $this->seller_since,
            'company'          => new CompanyResource($this->whenLoaded('company')),
        ];
    }
}
