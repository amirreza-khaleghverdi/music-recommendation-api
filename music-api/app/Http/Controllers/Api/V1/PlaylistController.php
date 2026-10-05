<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\PaginatesResults;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AddPlaylistTrackRequest;
use App\Http\Requests\Api\V1\ReorderPlaylistTracksRequest;
use App\Models\Playlist;
use App\Http\Requests\Api\V1\StorePlaylistRequest;
use App\Http\Requests\Api\V1\UpdatePlaylistRequest;
use App\Http\Resources\Api\V1\PlaylistResource;
use App\Http\Resources\Api\V1\TrackResource;
use App\Models\Track;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PlaylistController extends Controller
{
    use PaginatesResults;

    public function index(Request $request): AnonymousResourceCollection
    {
        $playlists = Playlist::query()
        ->when(
            $request->user() === null,
            fn ($query) => $query->where('is_public', true),
            fn ($query) => $query->where(
                fn ($query) => $query->where('is_public', true)
                    ->orWhere('user_id', $request->user()->id)
            )
        )
        ->when(
            $request->filled('search'),
            fn ($query) => $query->where('title', 'ilike',
            '%' . addcslashes($request->string('search')->toString(), '%_\\') . '%')
        )
        ->paginate($this->perPage($request));

        return PlaylistResource::collection($playlists);
    }

    public function show(Playlist $playlist): PlaylistResource
    {
        return new PlaylistResource($playlist->load('user'));
    }

    public function tracks(Request $request, Playlist $playlist): AnonymousResourceCollection
    {
        $tracks = $playlist->tracks()->paginate($this->perPage($request));
        return TrackResource::collection($tracks);
    }

    public function store(StorePlaylistRequest $request): JsonResponse
    {
        $playlist = $request->user()->playlists()->create($request->validated());
        return (new PlaylistResource($playlist))
        ->response()
        ->setStatusCode(Response::HTTP_CREATED);
    }
    
    public function update(UpdatePlaylistRequest $request, Playlist $playlist): PlaylistResource
    {
        $playlist->update($request->validated());
        return new PlaylistResource($playlist);
    }

    public function destroy(Playlist $playlist): Response
    {
        $playlist->delete();
        return response()->noContent();
    }

    public function addTrack(AddPlaylistTrackRequest $request, Playlist $playlist): Response
    {
        DB::transaction(function () use ($request, $playlist) {
            Playlist::whereKey($playlist->id)->lockForUpdate()->first();

            $end = (int) DB::table('playlist_track')
                ->where('playlist_id', $playlist->id)
                ->max('position') + 1;

            $position = min($request->validated('position') ?? $end, $end);

            DB::table('playlist_track')
                ->where('playlist_id', $playlist->id)
                ->where('position', '>=', $position)
                ->increment('position');

            $playlist->tracks()->attach($request->validated('track_id'), [
                'position' => $position,
            ]);
        });

        return response()->noContent();
    }

    public function removeTrack(Playlist $playlist, Track $track): Response
    {
        $playlist->tracks()->detach($track->id);
        return response()->noContent();
    }

    public function reorder(ReorderPlaylistTracksRequest $request, Playlist $playlist): Response
    {
        DB::transaction(function () use ($request, $playlist) {
            foreach ($request->validated('track_ids') as $index => $trackId) {
                $playlist->tracks()->updateExistingPivot($trackId, [
                    'position' => $index + 1,
                ]);
            }
        });
    
        return response()->noContent();
    }
}
