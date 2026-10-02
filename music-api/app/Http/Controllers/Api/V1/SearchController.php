<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SearchRequest;
use App\Http\Resources\Api\V1\AlbumResource;
use App\Http\Resources\Api\V1\ArtistResource;
use App\Http\Resources\Api\V1\TrackResource;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Track;

class SearchController extends Controller
{
    const LIMIT_PER_TYPE=10;

    public function __invoke(SearchRequest $request)
    {
        $term = $request->validated('q');

        $search = fn (string $model) => $model::query()
            ->search($term)
            ->orderByRelevance($term)
            ->limit(self::LIMIT_PER_TYPE)
            ->get();

        return response()->json([
            'data' => [
                'artists' => ArtistResource::collection($search(Artist::class)),
                'albums'  => AlbumResource::collection($search(Album::class)),
                'tracks'  => TrackResource::collection($search(Track::class)),
            ],
        ]);
    }
}
