<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackAudioFeatureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'track_id' => $this->track_id,
            'track' => $this->whenLoaded('track', fn() => new TrackResource($this->track)),
            'tempo_bpm' => $this->tempo_bpm,
            'energy' => $this->energy,
            'danceability' => $this->danceability,
            'valence' => $this->valence,
            'acousticness' => $this->acousticness,
            'instrumentalness' => $this->instrumentalness,
            'speechiness' => $this->speechiness,
            'liveness' => $this->liveness,
            'loudness_db' => $this->loudness_db,
            'key_signature' => $this->key_signature,
            'mode' => $this->mode,
            'time_signature' => $this->time_signature
        ];
    }
}
