<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'subject'         => $this->subject,
            'status'          => $this->status,
            'last_message_at' => $this->last_message_at,
            'user'            => $this->whenLoaded('user', fn () => ['id' => $this->user?->id, 'name' => $this->user?->name]),
            'messages'        => $this->whenLoaded('messages', fn () => $this->messages->map(fn ($m) => [
                'id'         => $m->id,
                'body'       => $m->body,
                'from_admin' => $m->from_admin,
                'mine'       => $request->user() && $request->user()->id === $m->sender_id,
                'created_at' => $m->created_at,
            ])),
            'created_at'      => $this->created_at,
        ];
    }
}
