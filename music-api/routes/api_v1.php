<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ArtistController;
use App\Http\Controllers\Api\V1\AlbumController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GenreController;
use App\Http\Controllers\Api\V1\PlaylistController;
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

                Route::get('/me', 'me')
                ->name('me');

                Route::patch("/me", 'me')
                ->name('me');

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

                Route::patch('/{artist}', 'patch')
                ->name('update');

                Route::delete('/{artist}', 'destroy')
                ->name('destroy');

                Route::post('/{artist}/follow', 'follow')
                ->name('follow');

                Route::delete('/{artist}/follow', 'unfollow')
                ->name('follow');
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
                ->name('destroy');
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
                ->name('store');


                Route::patch('/{track}', 'update')
                ->name('update');


                Route::delete('/{track}', 'destroy')
                ->name('destroy');

                Route::post('/{track}/like', 'like')
                ->name('like');

                Route::delete('/{track}/like', 'unlike')
                ->name('unlike');

                Route::post('/{}/listen', 'ListenEventController')
                ->name('listen-event-controller');
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

        //...playlist...//
        Route::controller(PlaylistController::class)
        ->prefix('/playlists')
        ->name('playlist.')
        ->group(function() {


                
        });
    });