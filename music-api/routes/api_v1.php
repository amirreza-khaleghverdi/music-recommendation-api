<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ArtistController;
use App\Http\Controllers\Api\V1\AlbumController;
// ...

Route::prefix('v1')->group(function () {
    Route::apiResource('artists', ArtistController::class)->except(['store','update','destroy'])
        ->missing(fn () => abort(404));
    // etc.
});