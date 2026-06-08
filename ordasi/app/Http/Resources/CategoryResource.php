<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'parent_id'   => $this->parent_id,
            'name'        => $this->name,
            'description' => $this->description,
            'path'        => $this->when($this->relationLoaded('parent') || $this->parent_id === null, fn () => $this->path),
            'children'    => CategoryResource::collection($this->whenLoaded('childrenRecursive')),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
