<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Game\GameController;
use App\Http\Controllers\Api\Game\RoomController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\SignUpController;


Route::middleware(['guest'])->prefix('auth')->group(function () {
    Route::post("login", LoginController::class);
    Route::post("register", SignUpController::class);
});


Route::middleware('auth:sanctum')->group(function () {


    Route::post('game/play', [GameController::class, 'play']);

    Route::prefix('room')->controller(RoomController::class)->group(function () {
        Route::post('create', 'create');
        Route::post('join', 'join');
        Route::get('list', 'list');
        Route::post('get-state', 'state');
    });



    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Broadcast::routes(['middleware' => ['auth:sanctum', 'api']]);
