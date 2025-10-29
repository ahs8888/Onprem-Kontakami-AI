<?php

use App\Http\Controllers\Admin\TicketImportController;
use Illuminate\Support\Facades\Route;

/**
 * Ticket Import Module Routes
 * 
 * Handles CSV/Excel import for bulk ticket linking
 * All routes are prefixed with /admin/ticket-import
 */
Route::middleware(['auth:admin'])->prefix('admin/ticket-import')->name('ticket-import.')->group(function () {
    
    /**
     * Main import page
     * GET /admin/ticket-import
     */
    Route::get('/', [TicketImportController::class, 'index'])->name('index');
    
    /**
     * Parse uploaded CSV/Excel file
     * POST /admin/ticket-import/parse
     * 
     * Accepts: multipart/form-data with 'file' field
     * Returns: { headers, rows, row_count }
     */
    Route::post('/parse', [TicketImportController::class, 'parseFile'])->name('parse');
    
    /**
     * Validate column mapping and match recordings
     * POST /admin/ticket-import/validate
     * 
     * Accepts: { column_mapping, csv_data }
     * Returns: { results: [{ status, message, recording_id }] }
     */
    Route::post('/validate', [TicketImportController::class, 'validateMapping'])->name('validate');
    
    /**
     * Perform bulk ticket linking
     * POST /admin/ticket-import/bulk-link
     * 
     * Accepts: { records, column_mapping }
     * Returns: { linked_count, failed_count, errors }
     */
    Route::post('/bulk-link', [TicketImportController::class, 'bulkLink'])->name('bulk-link');
    
    /**
     * Download not-found recordings report
     * GET /admin/ticket-import/not-found-report
     * 
     * Returns: CSV file download
     */
    Route::get('/not-found-report', [TicketImportController::class, 'downloadNotFoundReport'])->name('not-found-report');
});
