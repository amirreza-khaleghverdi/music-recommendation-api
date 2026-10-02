<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\PaginatesResults;
use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Http\Requests\Api\V1\StoreAlbumRequest;
use App\Http\Requests\Api\V1\UpdateAlbumRequest;
use App\Http\Resources\Api\V1\AlbumResource;
use App\Http\Resources\Api\V1\ArtistResource;
use App\Http\Resources\Api\V1\TrackResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlbumController extends Controller
{
    use PaginatesResults;
    
    public function index(Request $request): AnonymousResourceCollection
    {
        $albums = Album::query()
            ->when(
                $request->filled('search'),
                fn ($query) => $query->search($request->string('search')->toString())
            )
            ->when(
                $request->filled('album_type'),
                fn ($query) => $query->where('album_type', $request->string('album_type'))
            )
            ->paginate($this->perPage($request));

        return AlbumResource::collection($albums);
    }

    public function show(Album $album): AlbumResource
    {
        return new AlbumResource($album->load([
            'tracks' => fn ($query) => $query->orderBy('disc_number')->orderBy('track_number'),
            'artists'
            ])
        );
    }

    public function tracks(Request $request, Album $album): AnonymousResourceCollection
    {
        $tracks = $album->tracks()->orderBy('disc_number')->orderBy('track_number')->paginate($this->perPage($request));
        return TrackResource::collection($tracks);
    }

    public function artists(Request $request, Album $album): AnonymousResourceCollection
    {
        $artists = $album->artists()->paginate($this->perPage($request));
        return ArtistResource::collection($artists);
    }

    public function store(StoreAlbumRequest $request): JsonResponse
    {
        $album = Album::create($request->validated());
        return (new AlbumResource($album))
        ->response()
        ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateAlbumRequest $request, Album $album): AlbumResource
    {
        $album->update($request->validated());
        return new AlbumResource($album);
    }

    public function destroy(Album $album): Response
    {
        $album->delete();
        return response()->noContent();
    }
}
