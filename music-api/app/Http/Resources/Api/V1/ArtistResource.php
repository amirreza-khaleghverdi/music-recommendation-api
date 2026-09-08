<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'bio' => $this->bio,
            'country_code' => $this->country_code,
            'formed_year' => $this->formed_year,
            'image_url' => $this->image_url,
            'external_ids' => $this->external_ids,
        ];
    }
}
