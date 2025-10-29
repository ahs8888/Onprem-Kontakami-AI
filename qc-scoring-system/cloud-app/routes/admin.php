<?php

use App\Enum\PromptType;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\Analysis\AdminAnalysisController;
use App\Http\Controllers\RecordingAnalysis\PromptRecordingAnalysisController;


Route::redirect('/', 'sign-in');

Route::controller(AdminAuthController::class)
     ->prefix('sign-in')
     ->middleware(['idle-logout:false'])
     ->as('admin.auth.login.')
     ->group(function () {
          Route::get('/', 'index')->name('index');
     });

Route::middleware(['admin-auth', 'idle-logout'])
     ->group(function () {
          Route::get('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.auth.logout');
          Route::controller(AdminAnalysisController::class)
               ->prefix('analysis-record')
               ->as('admin.analysis-record.')
               ->group(function () {
                    Route::get('/', 'index')->name('index');
               });
     });

Route::middleware(['me-auth', 'idle-logout'])
     ->group(function () {
          Route::controller(PromptRecordingAnalysisController::class)
               ->prefix('recording-analysis/prompt')
               ->as('setup.recording-analysis.prompt.')
               ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/choise', 'choise')->name('choise');
                    Route::get('/create/{type}', 'create')->whereIn('type', PromptType::cases())->name('create');
                    Route::get('/edit/{type}/{uuid}', 'edit')->whereIn('type', PromptType::cases())->name('edit');
               });
     });