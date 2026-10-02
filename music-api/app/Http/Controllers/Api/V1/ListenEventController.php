<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RecordListenRequest;
use App\Http\Resources\Api\V1\ListenEventResource;
use App\Models\Track;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ListenEventController extends Controller
{
    public function store(RecordListenRequest $request, Track $track): JsonResponse
    {
        $listenEvent = $request->user()->listenEvents()->create([
            ...$request->validated(),
            'track_id'  => $track->id,
            'played_at' => now(),
        ]);

        return (new ListenEventResource($listenEvent))
        ->response()
        ->setStatusCode(Response::HTTP_CREATED);
    }
}
