<?php

namespace App\Http\Controllers\Api\V1;

use app\Http\Controllers\Concerns\PaginatesResults;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AddTrackArtistRequest;
use App\Models\Track;
use App\Http\Requests\Api\V1\StoreTrackRequest;
use App\Http\Requests\Api\V1\UpdateTrackRequest;
use App\Http\Resources\Api\V1\TrackAudioFeatureResource;
use App\Http\Resources\Api\V1\TrackResource;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TrackController extends Controller
{
    use PaginatesResults;

    public function index(Request $request): AnonymousResourceCollection
    {
        $tracks = Track::query()
        ->when(
            $request->filled('search'),
            fn ($query) => $query->search($request->string('search')->toString())
        )
        ->when(
            $request->filled('genre'),
            fn ($query) => $query->whereHas(
                'genres',
                fn ($query) => $query->where('genres.id', $request->integer('genre'))
            )
        )
        ->when(
            $request->filled('artist'),
            fn ($query) => $query->whereHas(
                'artists',
                fn ($query) => $query->where('artists.id', $request->integer('artist'))
            )
        )
        ->when(
            $request->has('explicit'),
            fn ($query) => $query->where('explicit', $request->boolean('explicit'))
        )
        ->paginate($this->perPage($request));

        return TrackResource::collection($tracks);
    }

    public function show(Track $track)
    {
        return new TrackResource($track->load(['album', 'artists', 'genres']));
    }

    public function audioFeatures(Request $request, Track $track)
    {
        abort_unless(
            $track->audioFeatures()->exists(),
            Response::HTTP_NOT_FOUND,
            'No audio features recorded for this track'
        );
        return new TrackAudioFeatureResource($track->audioFeatures());
    }

    public function store(StoreTrackRequest $request)
    {
        $track = Track::create($request->validated());
        return (new TrackResource($track))
        ->response()
        ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateTrackRequest $request, Track $track)
    {
        $track->update($request->validated());
        return new TrackResource($track);
    }

    public function destroy(Track $track)
    {
        $track->delete();
        return response()->noContent();
    }

    public function attachArtist(AddTrackArtistRequest $request, Track $track)
    {
        $track->artists()->attach(
            $request->validated('artist_id'),
            ['role' => $request->validated('role')]
        );

        return (new TrackResource($track->load('artists')))
        ->response()
        ->setStatusCode(Response::HTTP_CREATED);
    }

    public function detachArtist(Track $track, Artist $artist)
    {
        $track->artists()->detach($artist->id);
        return response()->noContent();
    }
}
