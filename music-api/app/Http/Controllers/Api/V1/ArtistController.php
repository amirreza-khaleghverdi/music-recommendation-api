<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\PaginatesResults;
use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Http\Requests\Api\V1\StoreArtistRequest;
use App\Http\Requests\Api\V1\UpdateArtistRequest;
use App\Http\Resources\Api\V1\AlbumResource;
use App\Http\Resources\Api\V1\ArtistResource;
use App\Http\Resources\Api\V1\TrackResource;
use App\Models\Concerns\FullTextSearchable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ArtistController extends Controller
{
    use PaginatesResults;

    public function index(Request $request): AnonymousResourceCollection
    {
        $artists = Artist::query()
        ->when(
            $request->filled('search'),
            fn ($query) => $query->search($request->string('search')->toString())
        )
        ->when(
            $request->filled('country_code'),
            fn ($query) => $query->where('country_code', $request->string('country_code'))
        )
        ->paginate($this->perPage($request));

        return ArtistResource::collection($artists);
    }

    public function show(Artist $artist): ArtistResource
    {
        return new ArtistResource($artist->load('albums'));
    }

    public function albums(Request $request, Artist $artist): AnonymousResourceCollection
    {
        $albums = $artist->albums()->paginate($this->perPage($request));
        return AlbumResource::collection($albums);
    }

    public function tracks(Request $request, Artist $artist): AnonymousResourceCollection
    {
        $tracks = $artist->tracks()->paginate($this->perPage($request));
        return TrackResource::collection($tracks);
    }

    public function store(StoreArtistRequest $request): JsonResponse
    {
        $artist = Artist::create($request->validated());

        return (new ArtistResource($artist))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateArtistRequest $request, Artist $artist): ArtistResource
    {
        $artist->update($request->validated());
        return new ArtistResource($artist);
    }

    public function destroy(Artist $artist): Response
    {
        $artist->delete();
        return response()->noContent();
    }
}
