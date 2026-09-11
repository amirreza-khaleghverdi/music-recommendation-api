<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListenEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'played_at' => $this->played_at,
            'ms_played' => $this->ms_played,
            'context_type' => $this->context_type,
            'context_id' => $this->context_id,
            'device_type' => $this->device_type,
            'session_id' => $this->session_id,
            'track' => $this->whenLoaded('track', fn() => new TrackResource($this->track))
        ];
    }
}
