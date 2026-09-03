<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ArtistController;
use App\Http\Controllers\Api\V1\AlbumController;
use App\Http\Controllers\Api\V1\AuthController;

// ...

Route::prefix('v1')
    ->name('Api.v1')
    ->group( function () {
        
        Route::controller(AuthController::class)
        ->prefix('auth')
        ->name('auth.')
        ->group(function (){

            Route::post('/register', 'register')
            ->middleware('throttle:6,1')
            ->name('register');

            Route::post('login', 'login')
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

        Route::controller(ArtistController::class)
        ->prefix('artists')
        ->name('artists.')
        ->group(function () {
           
            Route::get('/', 'index')
            ->name('index');

            Route::get('{artist}', 'show')
            ->name('show');

            Route::get('{artist}/albums', 'albums')
            ->name('albums');

            Route::get('{artist}/tracks', 'tracks')
            ->name('tracks');

            Route::middleware('auth:sanctum')->group(function() {

                Route::post('/', 'store')
                ->name('store');

                Route::patch('{arits}', 'patch')
                ->name('update');

                Route::delete('artists/{artist}')
                ->name('delete');

                Route::post('{artists}/follow', 'follow')
                ->name('follow');

                Route::delete('{artist}/follow', 'unfollow')
                ->name('follow');
            });
        });

        Route::controller(AlbumController::class)
        ->prefix('/albums')
        ->name('albums.')
        ->group(function() {

            Route::get('/', 'index')
            ->name('index');

            Route::get('{album}', 'show')
            ->name('show');

            Route::get('{album}/tracks', 'tracks')
            ->name('tracks');

            Route::middleware('auth:sanctum')->group(function() {

                Route::post('/', 'create')
                ->name('create');

                Route::patch('/{album}', 'update')
                ->name('update');

                Route::delete('/{album}', 'delete')
                ->name('delete');
            });
        });
    });