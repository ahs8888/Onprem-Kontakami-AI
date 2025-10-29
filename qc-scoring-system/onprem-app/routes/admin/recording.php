<?php

use App\Http\Controllers\Admin\RecordingController;
use Illuminate\Support\Facades\Route;

/**
 * Recording Module Routes
 * 
 * Handles recording upload, management, and transcription
 * All routes are prefixed with /admin/recording
 */
Route::middleware(['auth:admin'])->prefix('admin/recording')->name('recording.')->group(function () {
    
    /**
     * Recording list page
     * GET /admin/recording
     */
    Route::get('/', [RecordingController::class, 'index'])->name('index');
    
    /**
     * Recording detail page
     * GET /admin/recording/{id}
     */
    Route::get('/{id}', [RecordingController::class, 'detail'])->name('detail');
    
    /**
     * Transcript viewer page
     * GET /admin/recording/{id}/transcript/{detailId}
     */
    Route::get('/{id}/transcript/{detailId}', [RecordingController::class, 'transcript'])->name('transcript');
    
    /**
     * Retry recording processing
     * POST /admin/recording/{id}/retry
     */
    Route::post('/{id}/retry', [RecordingController::class, 'retry'])->name('retry');
    
    /**
     * Delete recording
     * DELETE /admin/recording/{id}
     */
    Route::delete('/{id}', [RecordingController::class, 'deleteFolder'])->name('delete');
});

/**
 * API Routes for Recording Module
 * Prefixed with /api/recordings
 */
Route::middleware(['auth:admin'])->prefix('api/recordings')->name('api.recordings.')->group(function () {
    
    /**
     * Get recordings datatable
     * GET /api/recordings/datatable
     */
    Route::get('/datatable', [RecordingController::class, 'datatable'])->name('datatable');
    
    /**
     * Get recording details datatable
     * GET /api/recordings/{id}/detail-datatable
     */
    Route::get('/{id}/detail-datatable', [RecordingController::class, 'detailDatatable'])->name('detail-datatable');
    
    /**
     * Upload recording folder
     * POST /api/recordings/store-folder
     */
    Route::post('/store-folder', [RecordingController::class, 'storeFolder'])->name('store-folder');
});
