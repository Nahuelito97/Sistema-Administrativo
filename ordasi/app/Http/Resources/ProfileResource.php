<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'dni'     => $this->dni,
            'ruc'     => $this->ruc,
            'address' => $this->address,
            'phone'   => $this->phone,
        ];
    }
}
