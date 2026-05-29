<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'slug'         => $this->slug,
            'status'       => $this->status,
            'excerpt'      => $this->excerpt,
            'body'         => $this->body,
            'image'        => $this->image,
            'published_at' => $this->published_at,
            'category_id'  => $this->category_id,
            'category'     => new CategoryResource($this->whenLoaded('category')),
            'tags'         => TagResource::collection($this->whenLoaded('tags')),
            'created_at'   => $this->created_at,
        ];
    }
}
