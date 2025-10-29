<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Data\RecordingDetail;
use App\Services\TicketLinkingService;
use Illuminate\Http\Request;

/**
 * Ticket Link Controller
 * 
 * RESTful API for ticket linking operations
 */
class TicketLinkController extends Controller
{
    protected TicketLinkingService $ticketService;

    public function __construct(TicketLinkingService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Get unlinked recordings count
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnlinkedCount()
    {
        $count = $this->ticketService->getUnlinkedCount();
        
        return response()->json([
            'count' => $count
        ]);
    }

    /**
     * Get unlinked recordings list with pagination
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnlinkedList(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $recordings = $this->ticketService->getUnlinkedRecordings($perPage);
        
        return response()->json($recordings);
    }

    /**
     * Link single recording to ticket
     * 
     * @param Request $request
     * @param string $recordingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function linkSingle(Request $request, string $recordingId)
    {
        $request->validate([
            'ticket_id' => 'required|string|max:100',
            'ticket_url' => 'nullable|url',
            'customer_name' => 'nullable|string|max:255',
            'agent_name' => 'nullable|string|max:255',
            'call_intent' => 'nullable|string|max:100',
            'call_outcome' => 'nullable|string|max:100',
        ]);

        try {
            $recording = RecordingDetail::findOrFail($recordingId);
            
            $updated = $this->ticketService->linkRecordingToTicket(
                $recording, 
                $request->all()
            );

            return response()->json([
                'success' => true,
                'message' => 'Ticket linked successfully',
                'recording' => $updated
            ]);
        } catch (\Exception $e) {
            \Log::error('Single Link Error', [
                'recording_id' => $recordingId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to link ticket: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Unlink recording from ticket
     * 
     * @param string $recordingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlink(string $recordingId)
    {
        try {
            $recording = RecordingDetail::findOrFail($recordingId);
            
            $this->ticketService->unlinkRecording($recording);

            return response()->json([
                'success' => true,
                'message' => 'Ticket unlinked successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Unlink Error', [
                'recording_id' => $recordingId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to unlink ticket: ' . $e->getMessage()
            ], 422);
        }
    }
}
