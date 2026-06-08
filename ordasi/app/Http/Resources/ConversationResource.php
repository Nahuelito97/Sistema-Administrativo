<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $me = $request->user();

        return [
            'id'              => $this->id,
            'order_id'        => $this->order_id,
            'company_id'      => $this->company_id,
            'company'         => new CompanyResource($this->whenLoaded('company')),
            'buyer'           => $this->whenLoaded('buyer', fn () => ['id' => $this->buyer?->id, 'name' => $this->buyer?->name]),
            'last_message_at' => $this->last_message_at,
            'last_message'    => $this->whenLoaded('messages', fn () => optional($this->messages->last())->body),
            // Mensajes no leídos que NO envié yo.
            'unread'          => $this->when(
                $this->relationLoaded('messages') && $me,
                fn () => $this->messages->whereNull('read_at')->where('sender_id', '!=', $me->id)->count()
            ),
            'created_at'      => $this->created_at,
        ];
    }
}
