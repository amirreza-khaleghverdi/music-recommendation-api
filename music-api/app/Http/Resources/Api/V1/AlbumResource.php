<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlbumResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'album_type' =>$this->album_type,
            'release_date' => $this->release_date,
            'cover_image' => $this->cover_image_url,
            'tracks' => TrackResource::collection($this->whenLoaded('tracks')),
            'artists' => ArtistResource::collection($this->whenLoaded('artists'))
        ];
    }
}
