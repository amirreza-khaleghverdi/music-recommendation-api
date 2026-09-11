<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->when($request->user()?->id === $this->id, $this->email),
            'display_name' => $this->display_name,
            'role' => $this->role,
            'playlists' => PlaylistResource::collection($this->whenLoaded('playlists')),
            'liked_tracks' => TrackResource::collection($this->whenLoaded('likedTracks')),
            'followed_artists' => ArtistResource::collection($this->whenLoaded('followedArtists'))
        ];
    }
}
