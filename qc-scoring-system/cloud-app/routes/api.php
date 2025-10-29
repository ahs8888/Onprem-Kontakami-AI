<?php

use App\Http\Controllers\Api\ApiConnectionController;
use App\Http\Controllers\Api\ApiRecordingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api-auth'])
     ->prefix('external/v1')
     ->group(function () {
          Route::post('/ping', [ApiConnectionController::class, 'index']);
          Route::controller(ApiRecordingController::class)
               ->prefix('recording')
               ->group(function () {
                    Route::post('/', 'store');
                    Route::post('/inject/{uuid}', 'inject');
                    Route::delete('/{uuid}', 'destroy');
               });
     });