<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'product_id'  => $this->product_id,
            'question'    => $this->question,
            'answer'      => $this->answer,
            'is_answered' => $this->answered_at !== null,
            'answered_at' => $this->answered_at,
            'asked_by'    => $this->whenLoaded('user', fn () => $this->user?->name),
            'product'     => new ProductResource($this->whenLoaded('product')),
            'created_at'  => $this->created_at,
        ];
    }
}
