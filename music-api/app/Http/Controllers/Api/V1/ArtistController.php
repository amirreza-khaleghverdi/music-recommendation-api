<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Http\Requests\Api\V1\StoreArtistRequest;
use App\Http\Requests\Api\V1\UpdateArtistRequest;
use App\Http\Resources\Api\V1\AlbumResource;
use App\Http\Resources\Api\V1\ArtistResource;
use App\Http\Resources\Api\V1\TrackResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response as HttpResponse;
use Laravel\Mcp\Request;
use Illuminate\Http\Response;

class ArtistController extends Controller
{
    private const MAX_PER_PAGE = 100;

    public function index()
    {
        
    }

    public function show(Artist $artist)
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
        ->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    public function update(UpdateArtistRequest $request, Artist $artist): ArtistResource
    {
        $artist = Artist::update($request->validated());
        return new ArtistResource($artist);
    }

    public function destroy(Artist $artist): Response
    {
        $artist->delete();
        return response()->noContent();
    }

    private function perPage(Request $request): int
    {
        return min($request->integer('per_page', 15), self::MAX_PER_PAGE);
    }
}
