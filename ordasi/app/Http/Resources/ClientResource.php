<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'dni'        => $this->dni,
            'ruc'        => $this->ruc,
            'address'    => $this->address,
            'phone'      => $this->phone,
            'email'      => $this->email,
            'created_at' => $this->created_at,
        ];
    }
}
