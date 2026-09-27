<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\PaginatesResults;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\GenreResource;
use App\Http\Resources\Api\V1\TrackResource;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GenreController extends Controller
{
    use PaginatesResults;

    public function index(): AnonymousResourceCollection
    {
        return GenreResource::collection(Genre::all());
    }

    public function show(Genre $genre): GenreResource
    {
        return new GenreResource($genre);
    }

    public function tracks(Request $request, Genre $genre): AnonymousResourceCollection
    {
        $tracks = $genre->tracks()->paginate($this->perPage($request));
        return TrackResource::collection($tracks);
    }
}
