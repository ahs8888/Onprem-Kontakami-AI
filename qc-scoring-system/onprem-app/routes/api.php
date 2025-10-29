<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AccessTokenMiddleware;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Auth\AuthLoginController;
use App\Http\Controllers\Admin\RecordingController;
use App\Http\Controllers\Jobs\TranscriptController;
use App\Http\Controllers\Auth\AuthRegisterController;
use App\Http\Controllers\Auth\AuthResetPasswordController;
use App\Http\Controllers\Api\RecordingController as ApiRecordingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::controller(AuthRegisterController::class)
    ->prefix('register')
    ->as('auth.register.')
    ->group(function () {
        Route::post('store', 'store')->name('store');
    });
Route::controller(AuthLoginController::class)
    ->prefix('login')
    ->as('auth.login.')
    ->group(function () {
        Route::post('/store', 'store')->name('store');
    });
Route::controller(AuthResetPasswordController::class)
    ->prefix('reset-password')
    ->as('auth.reset-password.')
    ->group(function () {
        Route::post('/store', 'store')->name('store');
    });

Route::middleware(['me-auth'])
    ->group(function () {
        Route::post("setting/clouds-location", [SettingController::class, 'postClouds'])->name("api.setting.clouds-location");
        Route::post("setting/access-token", [SettingController::class, 'postAccessToken'])->name("api.setting.access-token");
        Route::post("setting/personal-setting", [SettingController::class, 'postPersonal'])->name("api.setting.personal-setting");

        Route::get("recordings/datatable", [RecordingController::class, 'datatable'])->name("api.recordings.datatable");
        Route::get("recordings/detail/{id}/datatable", [RecordingController::class, 'detailDatatable'])->name("api.recordings.detail-datatable");

        Route::post("recordings/store-folder", [RecordingController::class, 'storeFolder'])->name("api.recordings.store-folder");
        Route::post("recordings/delete-folder", [RecordingController::class, 'deleteFolder'])->name("api.recordings.delete-folder");

        Route::post('/transcript-progress', [TranscriptController::class, 'remainingTranscriptProcess'])->name('transcript.remaining');
    });

Route::middleware(AccessTokenMiddleware::class)
    ->prefix("v1")
    ->group(function () {
        Route::post("recordings/store-folder", [ApiRecordingController::class, 'storeFolder']);
    });
