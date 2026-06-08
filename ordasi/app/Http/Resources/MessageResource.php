<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'body'       => $this->body,
            'sender_id'  => $this->sender_id,
            'mine'       => $request->user() && $request->user()->id === $this->sender_id,
            'sender'     => $this->whenLoaded('sender', fn () => $this->sender?->name),
            'read'       => $this->read_at !== null,
            'created_at' => $this->created_at,
        ];
    }
}
