<?php

namespace App\Jobs;

use App\Models\Data\Setting;
use App\Models\Data\Recording;
use App\Models\Data\RecordingDetail;
use App\Services\CloudTransferService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateCloudTicketInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $recordingId;
    public $recordingDetailIds;

    /**
     * Create a new job instance.
     * 
     * @param string $recordingId - The recording folder ID
     * @param array|null $recordingDetailIds - Specific recording detail IDs to update, or null for all
     */
    public function __construct($recordingId, $recordingDetailIds = null)
    {
        $this->recordingId = $recordingId;
        $this->recordingDetailIds = $recordingDetailIds;
    }

    /**
     * Execute the job.
     * 
     * This job updates ticket information in the cloud for recordings that:
     * 1. Already transferred to cloud (transfer_cloud = 1)
     * 2. Now have ticket information (ticket_id is not null)
     */
    public function handle(): void
    {
        $token = Setting::where("key", "token")->value("value");
        $recordingId = $this->recordingId;
        $recording = Recording::where("id", $recordingId)->whereNotNull("clouds_uuid")->first();
        $cloudService = new CloudTransferService();

        if (!$recording) {
            Log::warning("UpdateCloudTicketInfo: Recording not found or no cloud UUID", [
                'recording_id' => $recordingId
            ]);
            return;
        }

        // Get recordings that need ticket info update
        $query = RecordingDetail::where("recording_id", $recordingId)
            ->where("transfer_cloud", 1)
            ->whereNotNull("ticket_id");
        
        // If specific recording detail IDs provided, filter by them
        if ($this->recordingDetailIds) {
            $query->whereIn('id', $this->recordingDetailIds);
        }
        
        $recs = $query->orderBy("sort", "asc")->get();

        if ($recs->isEmpty()) {
            Log::info("UpdateCloudTicketInfo: No recordings need ticket update", [
                'recording_id' => $recordingId
            ]);
            return;
        }

        // Prepare files with updated ticket information
        $files = $cloudService->prepareRecordingsBatch($recs);
        $recIds = $recs->pluck('id')->toArray();

        Log::info("UpdateCloudTicketInfo: Preparing to update cloud", [
            'recording_id' => $recordingId,
            'clouds_uuid' => $recording->clouds_uuid,
            'count' => count($files)
        ]);

        if (!$token) {
            Log::error("UpdateCloudTicketInfo: No cloud token found");
            return;
        }

        try {
            // Send ticket updates to cloud using the inject endpoint with ticket info
            $response = Http::withToken($token)
                ->post(config("services.clouds_url")."/api/external/v1/recording/update-ticket/".$recording->clouds_uuid, [
                    "files" => $files
                ]);

            if ($response->successful()) {
                Log::info("UpdateCloudTicketInfo: Successfully updated cloud ticket info", [
                    'recording_id' => $recordingId,
                    'clouds_uuid' => $recording->clouds_uuid,
                    'updated_count' => count($recIds)
                ]);
                
                // Optionally mark these as synced or add a last_synced_at timestamp
                // For now, we just log success
            } else {
                Log::error("UpdateCloudTicketInfo: Failed to update cloud", [
                    'recording_id' => $recordingId,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                
                Recording::where("id", $recordingId)->update([
                    'error_log' => array_merge(
                        $recording->error_log ?? [],
                        ['ticket_update_error' => $response->json()]
                    )
                ]);
            }
        } catch (\Exception $e) {
            Log::error("UpdateCloudTicketInfo: Exception occurred", [
                'recording_id' => $recordingId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
