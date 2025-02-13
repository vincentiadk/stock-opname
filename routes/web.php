<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TaggingController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\RakController;
use App\Http\Controllers\ReportController;
use App\Http\Middleware\ProtectLoginMiddleware;

Route::get('/login', [LoginController::class, 'index']);
Route::get('/', [LoginController::class, 'index']);
Route::group(['middleware' => ProtectLoginMiddleware::class], function () {
    Route::post('/login', [LoginController::class, 'submit']);
    Route::get('/tagging', [TaggingController::class, 'index']);
    Route::get('/collection/search', [TaggingController::class, 'searchItem']);


    Route::get('/setting', [SettingController::class, 'index']);

    Route::get('/rak', [RakController::class, 'index']);
    Route::get('/rak/datatable', [RakController::class, 'datatable']);

    Route::get('/report', [ReportController::class, 'index']);
    Route::get('/report/datatable', [ReportController::class, 'datatable']);
});