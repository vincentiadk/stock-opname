<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\TaggingController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShelvingController;
use App\Http\Controllers\RakController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Middleware\ProtectLoginMiddleware;

Route::get('/login', [LoginController::class, 'index']);
Route::get('/', [LoginController::class, 'index']);
Route::post('/login', [LoginController::class, 'submit']);
Route::get('/logout', [LoginController::class, 'logout']);
Route::group(['middleware' => ProtectLoginMiddleware::class], function () {
    Route::get('/home', [DashboardController::class, 'index']);

    Route::get('/tagging', [TaggingController::class, 'index']);
    Route::get('/tagging/search', [TaggingController::class, 'searchItem']);
    Route::post('/tagging/save', [TaggingController::class, 'save']);
    Route::post('/tagging/save-masalah', [TaggingController::class, 'saveMasalah']);
    Route::post('/tagging/save-not-found', [TaggingController::class, 'saveNotFound']);
    Route::post('/tagging/save-lepas-tagging', [TaggingController::class, 'saveLepasTagging']);

    Route::get('/setting', [SettingController::class, 'index']);
    Route::get('/setting/location', [SettingController::class, 'getLocation']);
    Route::post('/setting/location', [SettingController::class, 'saveLocation']);
    Route::get('/setting/location-shelf/{id}', [SettingController::class, 'getLocationShelf']);
    Route::post('/setting/location-shelf', [SettingController::class, 'saveLocationShelf']);
    Route::get('/setting/location-rugs/{id}', [SettingController::class, 'getLocationRugs']);
    Route::post('/setting/location-rugs', [SettingController::class, 'saveLocationRugs']);
    Route::get('/setting/stockopname', [SettingController::class, 'getStockopname']);
    Route::post('/setting/stockopname', [SettingController::class, 'saveStockopname']);

    Route::get('/stock-opname', [StockOpnameController::class, 'index']);
    Route::post('/stock-opname/save', [StockOpnameController::class, 'save']);
    Route::get('/stock-opname/synchronize', [StockOpnameController::class, 'synchronize']);

    Route::get('/shelving', [ShelvingController::class, 'index']);
    Route::post('/shelving/save', [ShelvingController::class, 'save']);
    Route::get('/shelving/synchronize', [ShelvingController::class, 'synchronize']);

    Route::post('/location/add/{table}', [LocationController::class, 'add']);
    Route::post('/location/delete/{table}/{id}', [LocationController::class, 'delete']);
    Route::post('/location/modify/{table}/{id}', [LocationController::class, 'modify']);

    Route::get('/rak', [RakController::class, 'index']);  
    Route::get('/rak/datatable', [RakController::class, 'datatable']);

    Route::get('/report', [ReportController::class, 'index']);
    Route::get('/report/datatable', [ReportController::class, 'datatable']);

});