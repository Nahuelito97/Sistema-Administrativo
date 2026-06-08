<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'body'            => $this->body,
            'attachment_url'  => $this->attachment_path ? Storage::disk('public')->url($this->attachment_path) : null,
            'attachment_name' => $this->attachment_name,
            'sender_id'       => $this->sender_id,
            'mine'            => $request->user() && $request->user()->id === $this->sender_id,
            'sender'          => $this->whenLoaded('sender', fn () => $this->sender?->name),
            'read'            => $this->read_at !== null,
            'created_at'      => $this->created_at,
        ];
    }
}
