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
        ->prefix('auth')
        ->name('auth.')
        ->group(function (){

            Route::post('/register', 'register')
            ->middleware('throttle:6,1')
            ->name('register');

            Route::post('/login', 'login')
            ->middleware('throttle:5,1')
            ->name('loign');


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
                ->middleware('can:create')
                ->name('store');

                Route::patch('/{artist}', 'patch')
                ->middleware('can:update')
                ->name('update');

                Route::delete('/{artist}', 'destroy')
                ->middleware('can:delete')
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
                ->middleware('can:create')
                ->name('store');

                Route::patch('/{album}', 'update')
                ->middleware('can:update')
                ->name('update');

                Route::delete('/{album}', 'destroy')
                ->middleware('can:delete')
                ->name('destroy');

                Route::post('/{album}/artists', 'albumArtists')
                ->middleware('can:update')
                ->name('album_artists');

                Route::delete('{album}/artists/{artist}', 'removeArtist')
                ->middleware('can:update')
                ->name('remove_artist');
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

            Route::get('/{track}/audio-feature', 'audioFeatures')
            ->name('audio-feature');


            Route::middleware('auth:sanctum')->group(function() {

                Route::post('/', 'store')
                ->middleware('can:create')
                ->name('store');


                Route::patch('/{track}', 'update')
                ->middleware('can:update')
                ->name('update');


                Route::delete('/{track}', 'destroy')
                ->middleware('can:delete')
                ->name('destroy');

                Route::post('/{track}/listen', '')
                ->middleware('can:create', 'throttle:60,1')
                ->name('');

                Route::post('/{track}/artists', '')
                ->middleware('can:update')
                ->name('');

                Route::delete('/{track}/artists/{artist}', '')
                ->middleware('can:update')
                ->name('');
            });

        });

        //...genre...//
        Route::controller(GenreController::class)
        ->prefix('/genres')
        ->name('genre.')
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

            Route::get('/liked-tracks', 'likedTracks')
            ->name('liked-tracks');

            Route::post('/liked-tracks/{track}', '')
            ->name('');

            Route::delete('/liked-tracks/{track}', '')
            ->name('');

            Route::get('/followed-artists', '')
            ->name('');

            Route::post('/followed-artists/{artist}', '')
            ->name('');

            Route::delete('/followed-artists/{artist}', '')
            ->name('');

            Route::get('/listen-history', '')
            ->name('');

            Route::get('/playlists', '')
            ->name('');
            });
        });

        //...playlist...//
        Route::controller(PlaylistController::class)
        ->prefix('/playlists')
        ->name('playlist.')
        ->group(function() {
    
            Route::get('/', 'index')
            ->middleware('can:viewAny')
            ->name('index');

            Route::get('/{playlist}', 'show')
            ->middleware('can:view')
            ->name('show');

            Route::get('/{playlist}/tracks', 'tracks')
            ->middleware('can:view')
            ->name('tracks');

            Route::middleware('auth:sanctum')->group(function() {

                Route::post('/', 'store')
                ->middleware('can:create')
                ->name('store');

                Route::post('/{playlist}', 'update')
                ->middleware('can:update')
                ->name('update');

                Route::delete('/{playlist}', 'delete')
                ->middleware('can:delete')
                ->name('delete');

                Route::post('/{playlist}/tracks', '')
                ->middleware('can:update')
                ->name('');

                Route::delete('/{playlist}/tracks/{track}', '')
                ->middleware('can:update')
                ->name('');

                Route::patch('/{playlist}/tracks/reorder', '')
                ->middleware('can:update')
                ->name('');
            });
        });

        Route::get('/search', [SearchController::class, 'search'])
        ->name('search');
    });