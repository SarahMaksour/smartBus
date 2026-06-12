<?php

use App\Http\Controllers\Api\BusController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\GPS\DeviceGPSController;
use App\Http\Controllers\GPS\GPSController;
use App\Http\Controllers\Route\RouteController;
use App\Http\Controllers\Settings\SettingsController;
use Illuminate\Support\Facades\Route;

//Auth
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::put('/auth/update', [AuthController::class, 'update'])->middleware('auth:sanctum');
Route::put('/auth/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
Route::post('/fcm-token', [AuthController::class, 'saveFcmToken'])->middleware('auth:sanctum');
//gps
Route::post('/gps/update-location', [GPSController::class, 'update']);
Route::get('/bus/live', [GPSController::class, 'live']);
Route::get('/bus/{id}/location', [GPSController::class, 'location']);
Route::post('/device/gps', [DeviceGPSController::class, 'receive']);

//route
Route::get('/routes', [RouteController::class, 'index']);
Route::get('/routes/{id}', [RouteController::class, 'show']);
Route::get('/routes/{id}/stops', [RouteController::class, 'stops']);
Route::get('/routes/{id}/path', [RouteController::class, 'path']);
//setting
Route::put('/settings/notifications', [SettingsController::class, 'updateNotifications'])->middleware('auth:sanctum');


//////////////////////neww
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::prefix('home')->name('home.')->group(function () {
        Route::get('nearby-stations', [HomeController::class, 'nearbyStations'])->name('nearby-stations');
        Route::get('map-data',        [HomeController::class, 'mapData'])->name('map-data');
    });

     Route::prefix('buses')->group(function () {
        Route::get('/', [BusController::class, 'index'])->name('index');
    });
 
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);

});