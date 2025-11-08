<?php

use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\Api\ListsController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\PilotController;
use App\Http\Controllers\Api\SpaceshipController;
use App\Http\Controllers\Api\SpaceshipPilotController;
use App\Http\Controllers\Api\UserImageController;
use Illuminate\Support\Facades\Route;

Route::post('auth/tokens', [AuthTokenController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthTokenController::class, 'destroy']);
    Route::post('users/{user}/image', [UserImageController::class, 'store']);

    Route::middleware('ability:viewer')->group(function () {
        Route::get('naves', [SpaceshipController::class, 'index']);
        Route::get('naves/{nave}', [SpaceshipController::class, 'show']);
        Route::get('naves/{nave}/maintenances', [SpaceshipController::class, 'listMaintenances']);
        Route::get('lists', [ListsController::class, 'index']);
        Route::get('lists/spaceships/without-pilot', [ListsController::class, 'spaceshipsWithoutPilot']);
        Route::get('lists/pilots/assigned-history', [ListsController::class, 'pilotAssignments']);
        Route::get('lists/pilots/current-active', [ListsController::class, 'currentActivePilots']);
        Route::get('lists/maintenances/by-dates', [ListsController::class, 'maintenancesBetweenDates']);
        Route::get('lists/{list}', [ListsController::class, 'listAll']);
        Route::get('lists/{list}/{id}', [ListsController::class, 'show']);
    });

    Route::middleware('ability:maintainer')->group(function () {
        Route::post('naves/{nave}/maintenances', [SpaceshipController::class, 'storeMaintenance']);
        Route::patch('naves/{nave}/maintenances/{maintenance}', [MaintenanceController::class, 'update']);
        Route::post('naves/{nave}/pilots', [SpaceshipPilotController::class, 'store']);
    });

    Route::middleware('ability:admin')->group(function () {
        Route::post('naves', [SpaceshipController::class, 'store']);
        Route::match(['put', 'patch'], 'naves/{nave}', [SpaceshipController::class, 'update']);
        Route::delete('naves/{nave}', [SpaceshipController::class, 'destroy']);

        Route::post('pilots', [PilotController::class, 'store']);
        Route::delete('pilots/{pilot}', [PilotController::class, 'destroy']);
    });
});

