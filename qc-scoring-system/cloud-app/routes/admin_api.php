<?php

use App\Enum\PromptType;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\Analysis\AdminAnalysisController;
use App\Http\Controllers\RecordingAnalysis\PromptRecordingAnalysisController;

Route::get('/abc', function () {
     return 'oke';
});
Route::middleware(['web_api', 'idle-logout:false'])->group(function () {
     Route::controller(AdminAuthController::class)
          ->prefix('sign-in')
          ->as('admin.auth.login.')
          ->group(function () {
               Route::post('/store', 'store')->name('store');
          });

     Route::middleware(['admin-auth'])
          ->group(function () {
               Route::controller(AdminAnalysisController::class)
                    ->prefix('analysis-record')
                    ->as('admin.analysis-record.')
                    ->group(function () {
                         Route::get('/datatable', 'datatable')->name('datatable');
                         Route::post('/', 'export')->name('export');
                    });
          });

     Route::middleware(['me-auth'])
          ->group(function () {
               Route::controller(PromptRecordingAnalysisController::class)
                    ->prefix('recording-analysis/prompt')
                    ->as('setup.recording-analysis.prompt.')
                    ->group(function () {
                         Route::post('/store/{type}', 'store')->whereIn('type', PromptType::cases())->name('store');
                         Route::get('/datatable', 'datatable')->name('datatable');
                         Route::delete('/destroy/{uuid}', 'destroy')->name('destroy');
                    });

          });
});
