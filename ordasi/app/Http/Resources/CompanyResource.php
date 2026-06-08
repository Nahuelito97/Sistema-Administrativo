<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'description'    => $this->description,
            'cuit'           => $this->cuit,
            'cond_iva'       => $this->cond_iva,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'address'        => $this->address,
            'logo'           => $this->logo,
            'logo_url'       => $this->logo_url,
            'banner'         => $this->banner,
            'banner_url'     => $this->banner_url,
            'social_network' => $this->social_network,
            'status'         => $this->status,
            'products_count' => $this->whenCounted('products'),
            'sellers_count'  => $this->whenCounted('sellers'),
            'created_at'     => $this->created_at,
        ];
    }
}
