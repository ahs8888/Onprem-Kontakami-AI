<?php

use App\Enum\PromptType;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Auth\AuthLoginController;
use App\Http\Controllers\Util\ProcessLogController;
use App\Http\Controllers\Auth\AuthRegisterController;
use App\Http\Controllers\Auth\AuthForgotPasswordController;
use App\Http\Controllers\AgentAnalysis\AgentProfilingController;
use App\Http\Controllers\RecordingAnalysis\AgentScoringController;
use App\Http\Controllers\RecordingAnalysis\RecordingTextController;
use App\Http\Controllers\AgentAnalysis\PromptAgentProfilingController;
use App\Http\Controllers\RecordingAnalysis\RecordingScoringController;
use App\Http\Controllers\RecordingAnalysis\PromptRecordingAnalysisController;


Route::middleware(['web_api','idle-logout:false'])->group(function () {
     Route::controller(AuthRegisterController::class)
          ->prefix('register')
          ->as('auth.register.')
          ->group(function () {
               Route::post('store', 'store')->name('store');
               Route::post('verification/{token}', 'postVerification')->name('verification');
               Route::post('/resend-otp/{token}', 'resentOtp')->name('otp-resend');
          });

     Route::controller(AuthForgotPasswordController::class)
          ->prefix('forgot-password')
          ->as('auth.forgot-password.')
          ->group(function () {
               Route::post('verification/{token}', 'postVerification')->name('verification');
               Route::post('/send-otp', 'sendOtp')->name('otp-send');
               Route::post('/update-password/{tokenId}', 'updatePassword')->name('update-password');
          });
     Route::controller(AuthLoginController::class)
          ->prefix('login')
          ->as('auth.login.')
          ->group(function () {
               Route::post('/store', 'store')->name('store');
          });

     Route::middleware(['me-auth'])
          ->group(function () {

               Route::post('/setting/personal', [ProfileController::class, 'updateProfile'])->name('setting.personal.update-profile');

               Route::controller(PromptRecordingAnalysisController::class)
                    ->prefix('recording-analysis/prompt')
                    ->as('setup.recording-analysis.prompt.')
                    ->group(function () {
                         Route::post('/store/{type}', 'store')->whereIn('type', PromptType::cases())->name('store');
                         Route::get('/datatable', 'datatable')->name('datatable');
                         Route::delete('/destroy/{uuid}', 'destroy')->name('destroy');
                    });

               Route::controller(RecordingTextController::class)
                    ->prefix('recording-analysis/recording-text')
                    ->as('setup.recording-analysis.recording-text.')
                    ->group(function () {
                         Route::get('/datatable', 'datatable')->name('datatable');
                         Route::get('/datatable/{uuid}', 'datatableItem')->name('datatable-item');
                         Route::post('/scoring', 'addScoring')->name('add-scoring');
                         Route::post('/stop-scoring/{uuid}', 'stopScoring')->name('stop-scoring');
                         Route::post('/export', 'export')->name('export');
                         Route::post('/export/{uuid}', 'exportItem')->name('export-item');
                         Route::delete('/destroy/{uuid}', 'destroy')->name('destroy');
                    });

               Route::controller(RecordingScoringController::class)
                    ->prefix('recording-analysis/recording-scoring')
                    ->as('setup.recording-analysis.recording-scoring.')
                    ->group(function () {
                         Route::get('/datatable', 'datatable')->name('datatable');
                         Route::get('/datatable/{uuid}', 'datatableItem')->name('datatable-item');
                         Route::post('/auto-analysis', 'autoAnalysis')->name('auto-analysis');
                         Route::post('/export', 'export')->name('export');
                         Route::post('/export/{uuid}', 'exportScoring')->name('export-scoring');
                         Route::post('/store', 'store')->name('store');
                         Route::post('/{uuid}/analysis/{scoring_uuid}/export', 'exportAnalysis')->name('export-analysis');
                         Route::post('/retry/{uuid}', 'retry')->name('retry');
                         Route::delete('/destroy/{uuid}', 'destroy')->name('destroy');
                    });
                    
               Route::controller(AgentScoringController::class)
                    ->prefix('recording-analysis/agent-scoring')
                    ->as('setup.recording-analysis.agent-scoring.')
                    ->group(function () {
                         Route::get('/datatable', 'datatable')->name('datatable');
                         Route::get('/datatable/{uuid}', 'datatableItem')->name('datatable-item');
                         Route::post('/analysis', 'analysis')->name('analysis');
                         Route::post('/retry/{uuid}', 'retry')->name('retry');
                         Route::post('/export/{uuid}', 'exportScoring')->name('export-scoring');
                         Route::post('/{scoring_uuid}/analysis/{item_uuid}/export', 'exportAnalysis')->name('export-analysis');
                         Route::delete('/destroy/{uuid}', 'destroy')->name('destroy');
                    });

               Route::controller(PromptAgentProfilingController::class)
                    ->prefix('agent-analysis/prompt')
                    ->as('setup.agent-analysis.prompt.')
                    ->group(function () {
                         Route::post('/store/{type}', 'store')->whereIn('type', PromptType::cases())->name('store');
                         Route::get('/datatable', 'datatable')->name('datatable');
                         Route::delete('/destroy/{uuid}', 'destroy')->name('destroy');
                    });

               Route::controller(AgentProfilingController::class)
                    ->prefix('agent-analysis/profiling')
                    ->as('setup.agent-analysis.profiling.')
                    ->group(function () {
                         Route::post('/export', 'export')->name('export');
                    });

               Route::get('/prosess/progress', [ProcessLogController::class, 'progress'])->name('process.progress');
               Route::post('/prosess/done/{uuid}', [ProcessLogController::class, 'done'])->name('process.done');
          });
});