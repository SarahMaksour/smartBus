<?php

use App\Http\Controllers\Api\BusController;
use App\Http\Controllers\Api\BusTrackingController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\RouteDetailsController;
use App\Http\Controllers\Api\RouteSearchController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\GPS\DeviceGPSController;
use App\Http\Controllers\GPS\GPSController; 
use App\Http\Controllers\Settings\SettingsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DriverLocationController;

//Auth
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('verify-otp',      [AuthController::class, 'verifyOtp']);
Route::post('reset-password',  [AuthController::class, 'resetPassword']);

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
/*
Route::get('/routes', [RouteController::class, 'index']);
Route::get('/routes/{id}', [RouteController::class, 'show']);
Route::get('/routes/{id}/stops', [RouteController::class, 'stops']);
Route::get('/routes/{id}/path', [RouteController::class, 'path']);*/
//setting
Route::put('/settings/notifications', [SettingsController::class, 'updateNotifications'])->middleware('auth:sanctum');


//////////////////////neww
   Route::prefix('v1/home')->name('home.')->group(function () {
        Route::get('nearby-stations', [HomeController::class, 'nearbyStations'])->name('nearby-stations');
        Route::get('map-data',        [HomeController::class, 'mapData'])->name('map-data');
    });
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
 

     Route::prefix('buses')->group(function () {
        Route::get('/', [BusController::class, 'index'])->name('index');
    });
 
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);
    Route::post('routes/search', [RouteSearchController::class, 'search']);
    Route::get('routes/details/{token}', [RouteDetailsController::class, 'show']);
    Route::get('routes/{id}', [RouteController::class, 'show']);
Route::get('buses/{id}/track', [BusTrackingController::class, 'track']);
});
Route::middleware('auth:sanctum')->prefix('v1/driver')->group(function () {
    Route::post('location', [DriverLocationController::class, 'store']);
    Route::post('start-trip', [DriverLocationController::class, 'startTrip']);
    Route::post('end-trip', [DriverLocationController::class, 'endTrip']);
});