<?php

use App\Http\Controllers\Admin\TicketLinkController;
use Illuminate\Support\Facades\Route;

/**
 * Ticket Linking API Routes
 * 
 * RESTful endpoints for ticket operations
 * All routes are prefixed with /api/v1/tickets
 */
Route::middleware(['auth:admin'])->prefix('api/v1/tickets')->name('api.tickets.')->group(function () {
    
    /**
     * Get unlinked recordings count
     * GET /api/v1/tickets/unlinked/count
     */
    Route::get('/unlinked/count', [TicketLinkController::class, 'getUnlinkedCount'])->name('unlinked.count');
    
    /**
     * Get unlinked recordings list with pagination
     * GET /api/v1/tickets/unlinked
     */
    Route::get('/unlinked', [TicketLinkController::class, 'getUnlinkedList'])->name('unlinked.list');
    
    /**
     * Link single recording to ticket
     * POST /api/v1/tickets/link/{recordingId}
     */
    Route::post('/link/{recordingId}', [TicketLinkController::class, 'linkSingle'])->name('link.single');
    
    /**
     * Unlink recording from ticket
     * DELETE /api/v1/tickets/unlink/{recordingId}
     */
    Route::delete('/unlink/{recordingId}', [TicketLinkController::class, 'unlink'])->name('unlink');
});
