<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\PaginatesResults;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdatePasswordRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\Api\V1\ArtistResource;
use App\Http\Resources\Api\V1\ListenEventResource;
use App\Http\Resources\Api\V1\PlaylistResource;
use App\Http\Resources\Api\V1\TrackResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\Artist;
use App\Models\Track;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProfileController extends Controller
{
    use PaginatesResults;

    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function update(UpdateProfileRequest $request): UserResource
    {
        $request->user()->update($request->validated());

        return new UserResource($request->user());
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['password' => $request->validated('password')]);

        $user->tokens()->delete();
        $token = $user->createToken('api')->plainTextToken;

        return (new UserResource($user))
            ->additional(['meta' => ['token' => $token]])
            ->response();
    }

    public function likedTracks(Request $request): AnonymousResourceCollection
    {
        $tracks = $request->user()->likedTracks()->paginate($this->perPage($request));

        return TrackResource::collection($tracks);
    }

    public function likeTrack(Request $request, Track $track): Response
    {
        $request->user()->likedTracks()->syncWithoutDetaching([
            $track->id
        ]);

        return response()->noContent();
    }

    public function unlikeTrack(Request $request, Track $track): Response
    {
        $request->user()->likedTracks()->detach($track->id);

        return response()->noContent();
    }

    public function followedArtists(Request $request): AnonymousResourceCollection
    {
        $artists = $request->user()->followedArtists()->paginate($this->perPage($request));

        return ArtistResource::collection($artists);
    }

    public function followArtist(Request $request, Artist $artist): Response
    {
        $request->user()->followedArtists()->syncWithoutDetaching([
            $artist->id
        ]);

        return response()->noContent();
    }

    public function unfollowArtist(Request $request, Artist $artist): Response
    {
        $request->user()->followedArtists()->detach($artist->id);

        return response()->noContent();
    }

    public function listenHistory(Request $request): AnonymousResourceCollection
    {
        $listenEvents = $request->user()
            ->listenEvents()
            ->with('track')
            ->latest('played_at')
            ->paginate($this->perPage($request));

        return ListenEventResource::collection($listenEvents);
    }

    public function playlists(Request $request): AnonymousResourceCollection
    {
        $playlists = $request->user()->playlists()->paginate($this->perPage($request));

        return PlaylistResource::collection($playlists);
    }
}