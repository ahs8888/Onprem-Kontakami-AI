<?php

use App\Enum\PromptType;
use App\Enum\AgentProfilingType;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Auth\AuthLoginController;
use App\Http\Controllers\Auth\AuthRegisterController;
use App\Http\Controllers\Auth\AuthForgotPasswordController;
use App\Http\Controllers\AgentAnalysis\AgentProfilingController;
use App\Http\Controllers\RecordingAnalysis\AgentScoringController;
use App\Http\Controllers\RecordingAnalysis\RecordingTextController;
use App\Http\Controllers\AgentAnalysis\PromptAgentProfilingController;
use App\Http\Controllers\RecordingAnalysis\RecordingScoringController;
use App\Http\Controllers\RecordingAnalysis\PromptRecordingAnalysisController;

/**
 * Authentication Route
 */
Route::redirect('/', 'login');
Route::controller(AuthRegisterController::class)
    ->prefix('register')
    ->as('auth.register.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/verification/{token}', 'verification')->name('otp-verification');
    });

Route::controller(AuthForgotPasswordController::class)
    ->prefix('forgot-password')
    ->as('auth.forgot-password.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/verification/{token}', 'verification')->name('otp-verification');
        Route::get('/change-password/{token}', 'changePassword')->name('change-password');
    });

Route::controller(AuthLoginController::class)
    ->prefix('login')
    ->middleware(['idle-logout:false'])
    ->as('auth.login.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
    });

Route::middleware(['me-auth', 'idle-logout'])
    ->group(function () {
        Route::get('/logout', [AuthLoginController::class, 'logout'])->name('auth.logout');


        Route::controller(PromptRecordingAnalysisController::class)
            ->prefix('recording-analysis/prompt')
            ->as('setup.recording-analysis.prompt.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/choise', 'choise')->name('choise');
                Route::get('/create/{type}', 'create')->whereIn('type', PromptType::cases())->name('create');
                Route::get('/edit/{type}/{uuid}', 'edit')->whereIn('type', PromptType::cases())->name('edit');
            });

        Route::controller(RecordingTextController::class)
            ->prefix('recording-analysis/recording-text')
            ->as('setup.recording-analysis.recording-text.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{uuid}', 'show')->name('show');
                Route::get('/scoring/{uuid}', 'scoring')->name('scoring');
            });

        Route::controller(RecordingScoringController::class)
            ->prefix('recording-analysis/recording-scoring')
            ->as('setup.recording-analysis.recording-scoring.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{uuid}', 'show')->name('show');
                Route::get('/{uuid}/analysis/{scoring_uuid}', 'analysis')->name('analysis');
            });

        Route::controller(AgentScoringController::class)
            ->prefix('recording-analysis/agent-scoring')
            ->as('setup.recording-analysis.agent-scoring.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/{uuid}', 'show')->name('show');
                Route::get('/{uuid}/scoring/{scoring_uuid}', 'scoring')->name('scoring');
            });

        Route::controller(PromptAgentProfilingController::class)
            ->prefix('agent-analysis/prompt')
            ->as('setup.agent-analysis.prompt.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/choise', 'choise')->name('choise');
                Route::get('/create/{type}', 'create')->whereIn('type', PromptType::cases())->name('create');
                Route::get('/edit/{type}/{uuid}', 'edit')->whereIn('type', PromptType::cases())->name('edit');
            });

        Route::controller(AgentProfilingController::class)
            ->prefix('agent-analysis/profiling')
            ->as('setup.agent-analysis.profiling.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/choise', 'choise')->name('choise');
                Route::get('/create/{type}', 'create')->whereIn('type', AgentProfilingType::cases())->name('create');
            });


        Route::inertia('/setting/cloud-location', 'setting/CloudLocation')->name('setting.cloud-location.index');
        Route::get('/setting/personal', [ProfileController::class, 'personal'])->name('setting.personal.index');
    });
