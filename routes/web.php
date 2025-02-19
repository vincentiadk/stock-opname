<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\TaggingController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\RakController;
use App\Http\Controllers\ReportController;
use App\Http\Middleware\ProtectLoginMiddleware;

Route::get('/login', [LoginController::class, 'index']);
Route::get('/', [LoginController::class, 'index']);
Route::post('/login', [LoginController::class, 'submit']);
Route::group(['middleware' => ProtectLoginMiddleware::class], function () {
    Route::get('/tagging', [TaggingController::class, 'index']);
    Route::get('/tagging/search', [TaggingController::class, 'searchItem']);
    Route::get('/tagging/save', [TaggingController::class, 'save']);

    Route::get('/setting', [SettingController::class, 'index']);
    Route::get('/setting/location', [SettingController::class, 'getLocation']);
    Route::post('/setting/location', [SettingController::class, 'saveLocation']);
    Route::get('/setting/location-shelf/{id}', [SettingController::class, 'getLocationShelf']);
    Route::post('/setting/location-shelf', [SettingController::class, 'saveLocationShelf']);
    Route::get('/setting/location-rugs/{id}', [SettingController::class, 'getLocationRugs']);
    Route::post('/setting/location-rugs', [SettingController::class, 'saveLocationRugs']);

    Route::post('/location/add/{table}', [LocationController::class, 'add']);
    Route::post('/location/delete/{table}/{id}', [LocationController::class, 'delete']);
    Route::post('/location/modify/{table}/{id}', [LocationController::class, 'modify']);

    Route::get('/rak', [RakController::class, 'index']);  
    Route::get('/rak/datatable', [RakController::class, 'datatable']);

    Route::get('/report', [ReportController::class, 'index']);
    Route::get('/report/datatable', [ReportController::class, 'datatable']);

});