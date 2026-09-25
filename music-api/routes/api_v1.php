<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ArtistController;
use App\Http\Controllers\Api\V1\AlbumController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GenreController;
use App\Http\Controllers\Api\V1\PlaylistController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\TrackController;

// ...

Route::prefix('v1')
    ->name('api.v1.')
    ->group( function () {

        //...auth...//
        Route::controller(AuthController::class)
        ->prefix('/auth')
        ->name('auth.')
        ->group(function (){

            Route::post('/register', 'register')
            ->middleware('throttle:6,1')
            ->name('register');

            Route::post('/login', 'login')
            ->middleware('throttle:5,1')
            ->name('login');


            Route::middleware('auth:sanctum')->group(function() {

                Route::post('/logout', 'logout')
                ->name('logout');

            });
        });

        //...artist...//
        Route::controller(ArtistController::class)
        ->prefix('/artists')
        ->name('artists.')
        ->group(function () {

            Route::get('/', 'index')
            ->name('index');

            Route::get('/{artist}', 'show')
            ->name('show');

            Route::get('/{artist}/albums', 'albums')
            ->name('albums');

            Route::get('/{artist}/tracks', 'tracks')
            ->name('tracks');

            Route::middleware('auth:sanctum')->group(function() {

                Route::post('/', 'store')
                ->name('store');

                Route::patch('/{artist}', 'update')
                ->name('update');

                Route::delete('/{artist}', 'destroy')
                ->middleware('can:delete,artist')
                ->name('destroy');
            });
        });

        //...album...//
        Route::controller(AlbumController::class)
        ->prefix('/albums')
        ->name('albums.')
        ->group(function() {

            Route::get('/', 'index')
            ->name('index');

            Route::get('/{album}', 'show')
            ->name('show');

            Route::get('/{album}/tracks', 'tracks')
            ->name('tracks');

            Route::get('/{album}/artists', 'artists')
            ->name('artists');

            Route::middleware('auth:sanctum')->group(function() {

                Route::post('/', 'store')
                ->name('store');

                Route::patch('/{album}', 'update')
                ->name('update');

                Route::delete('/{album}', 'destroy')
                ->middleware('can:delete,album')
                ->name('destroy');

                Route::post('/{album}/artists', 'attachArtist')
                ->name('artist.attach');

                Route::delete('/{album}/artists/{artist}', 'detachArtist')
                ->middleware('can:update,album')
                ->name('artist.detach');
            });
        });

        //...track...//
        Route::controller(TrackController::class)
        ->prefix('/tracks')
        ->name('tracks.')
        ->group(function() {

            Route::get('/', 'index')
            ->name('index');

            Route::get('/{track}', 'show')
            ->name('show');

            Route::get('/{track}/audio-features', 'audioFeatures')
            ->name('audio-features');


            Route::middleware('auth:sanctum')->group(function() {

                Route::post('/', 'store')
                ->name('store');


                Route::patch('/{track}', 'update')
                ->name('update');


                Route::delete('/{track}', 'destroy')
                ->middleware('can:delete,track')
                ->name('destroy');

                Route::post('/{track}/listen', 'listen')
                ->middleware('throttle:60,1')
                ->name('listen');

                Route::post('/{track}/artists', 'attachArtist')
                ->name('attach.artist');

                Route::delete('/{track}/artists/{artist}', 'detachArtist')
                ->middleware('can:update,track')
                ->name('detach.artist');
            });

        });

        //...genre...//
        Route::controller(GenreController::class)
        ->prefix('/genres')
        ->name('genres.')
        ->group(function() {

            Route::get('/', 'index')
            ->name('index');

            Route::get('/{genre}', 'show')
            ->name('show');

            Route::get('/{genre}/tracks', 'tracks')
            ->name('tracks');
        });

        //...me...//
        Route::controller(ProfileController::class)
        ->prefix('/me')
        ->name('me.')
        ->group(function() {

            Route::middleware('auth:sanctum')->group(function() {

            Route::get('/', 'show')
            ->name('show');

            Route::patch('/', 'update')
            ->name('update');

            Route::patch('/password', 'updatePassword')
            ->middleware('throttle:5,1')
            ->name('Password.update');

            Route::get('/liked-tracks', 'likedTracks')
            ->name('liked-tracks.index');

            Route::post('/liked-tracks/{track}', 'likeTrack')
            ->name('liked-tracks.attach');

            Route::delete('/liked-tracks/{track}', 'unlikeTrack')
            ->name('liked-tracks.detach');

            Route::get('/followed-artists', 'followedArtists')
            ->name('followed-artists.index');

            Route::post('/followed-artists/{artist}', 'followArtist')
            ->name('followed-artists.attach');

            Route::delete('/followed-artists/{artist}', 'unfollowArtist')
            ->name('followed-artists.detach');

            Route::get('/listen-history', 'listenHistory')
            ->name('listen-history');

            Route::get('/playlists', 'playlists')
            ->name('playlists');
            });
        });

        //...playlist...//
        Route::controller(PlaylistController::class)
        ->prefix('/playlists')
        ->name('playlists.')
        ->group(function() {

            Route::get('/', 'index')
            ->middleware('can:viewAny,App\Models\Playlist')
            ->name('index');

            Route::get('/{playlist}', 'show')
            ->middleware('can:view,playlist')
            ->name('show');

            Route::get('/{playlist}/tracks', 'tracks')
            ->middleware('can:view,playlist')
            ->name('tracks');

            Route::middleware('auth:sanctum')->group(function() {

                Route::post('/', 'store')
                ->name('store');

                Route::patch('/{playlist}', 'update')
                ->name('update');

                Route::delete('/{playlist}', 'destroy')
                ->middleware('can:delete,playlist')
                ->name('destroy');

                Route::post('/{playlist}/tracks', 'addTrack')
                ->name('tracks.attach');

                Route::delete('/{playlist}/tracks/{track}', 'removeTrack')
                ->middleware('can:update,playlist')
                ->name('tracks.detach');

                Route::patch('/{playlist}/tracks/reorder', 'reorder')
                ->name('tracks.reorder');
            });
        });

        Route::get('/search', SearchController::class)->name('search');
    });