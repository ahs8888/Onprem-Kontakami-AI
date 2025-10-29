<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Auth\AuthLoginController;
use App\Http\Controllers\Admin\RecordingController;
use App\Http\Controllers\Admin\TicketImportController;
use App\Http\Controllers\Jobs\TranscriptController;
use App\Http\Controllers\Auth\AuthRegisterController;
use App\Http\Controllers\Auth\AuthResetPasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


/**
 * Authentication Route
 */
Route::controller(AuthRegisterController::class)
    ->prefix('register')
    ->as('auth.register.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
    });

Route::controller(AuthLoginController::class)
    ->prefix('login')
    ->as('auth.login.')
    ->middleware(['idle-logout:false'])
    ->group(function () {
        Route::get('/', 'index')->name('index');
    });

Route::controller(AuthResetPasswordController::class)
    ->prefix('reset-password')
    ->as('auth.reset-password.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
    });

Route::middleware(['me-auth','idle-logout'])
    ->group(function () {
        Route::get('/logout', [AuthLoginController::class, 'logout'])->name('auth.logout');
        Route::get('/', [RecordingController::class, 'redirect'])->name("dashboard");
        Route::get('/recordings', [RecordingController::class, 'index'])->name("recordings.index");
        Route::get('/recordings/{id}', [RecordingController::class, 'detail'])->name("recordings.detail");
        Route::post('/recordings/{id}/retry', [RecordingController::class, 'retry'])->name("recordings.retry");
        Route::get('/recordings/{id}/transcript/{detailId}', [RecordingController::class, 'transcript'])->name("recordings.transcript");
        Route::get('/setting/clouds-location', [SettingController::class, 'clouds'])->name("setting.clouds-location");
        Route::get('/setting/access-token', [SettingController::class, 'accessToken'])->name("setting.access-token");
        Route::get('/setting/personal-setting', [SettingController::class, 'personal'])->name("setting.personal-setting");
    });
