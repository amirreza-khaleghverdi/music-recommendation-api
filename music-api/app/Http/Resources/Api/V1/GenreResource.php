<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GenreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' =>$this->name,
            'parent_genre_id' => $this->parent_genre_id,
            'parent' => $this->whenLoaded('parent', fn() => new GenreResource($this->parent))
        ];
    }
}
