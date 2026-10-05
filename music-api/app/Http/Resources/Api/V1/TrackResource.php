<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'track_number' => $this->track_number,
            'disc_number' => $this->disc_number,
            'duration_ms' => $this->duration_ms,
            'isrc' => $this->isrc,
            'explicit' => $this->explicit,
            'external_ids' => $this->external_ids,
            'album' => $this->whenLoaded('album', fn () => new AlbumResource($this->album)),
            'artists' => ArtistResource::collection($this->whenLoaded('artists')),
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'audio_features' => $this->whenLoaded('audioFeatures', fn() => new TrackAudioFeatureResource($this->audioFeatures)),
            'likes_count' => $this->whenCounted('likes')
        ];
    }
}
