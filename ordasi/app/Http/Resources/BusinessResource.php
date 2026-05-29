<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'logo'        => $this->logo,
            'mail'        => $this->mail,
            'address'     => $this->address,
            'ruc'         => $this->ruc,
        ];
    }
}
