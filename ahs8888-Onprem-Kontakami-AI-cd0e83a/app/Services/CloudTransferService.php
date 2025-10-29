<?php

namespace App\Services;

use App\Models\Data\RecordingDetail;
use Illuminate\Support\Collection;

class CloudTransferService
{
    /**
     * Prepare recording data for cloud transfer including ticket information
     * 
     * @param RecordingDetail $recording
     * @param int $tokenCount
     * @return array
     */
    public function prepareRecordingData(RecordingDetail $recording, int $tokenCount = 0): array
    {
        $relativePath = preg_replace('/^\/?storage\//', '', $recording->file);
        $fullPath = storage_path("app/public/" . $relativePath);
        $sizeInBytes = file_exists($fullPath) ? filesize($fullPath) : 0;
        $sizeFormatted = formatBytes($sizeInBytes);
        
        $data = [
            'filename' => $recording->name,
            'size' => $sizeFormatted,
            'token' => $tokenCount,
            'transcribe' => $recording->transcript
        ];
        
        // Add ticket information if available
        if ($recording->ticket_id) {
            $data['ticket_info'] = [
                'ticket_id' => $recording->ticket_id,
                'ticket_url' => $recording->ticket_url,
                'customer_name' => $recording->customer_name,
                'agent_name' => $recording->agent_name,
                'call_intent' => $recording->call_intent,
                'call_outcome' => $recording->call_outcome,
                'display_name' => $recording->display_name,
                'linked_at' => $recording->linked_at?->toIso8601String()
            ];
        }
        
        return $data;
    }
    
    /**
     * Prepare multiple recordings for cloud transfer
     * 
     * @param Collection $recordings
     * @return array
     */
    public function prepareRecordingsBatch(Collection $recordings): array
    {
        $files = [];
        
        foreach ($recordings as $recording) {
            $files[] = $this->prepareRecordingData(
                $recording,
                $recording->token ?? 0
            );
        }
        
        return $files;
    }
    
    /**
     * Get recordings that have ticket updates and need to be synced to cloud
     * 
     * @param string $recordingId
     * @return Collection
     */
    public function getRecordingsNeedingTicketSync(string $recordingId): Collection
    {
        // Get recordings that:
        // 1. Already transferred to cloud (transfer_cloud = 1)
        // 2. Have ticket information (ticket_id is not null)
        // 3. Have been linked recently or need update
        
        return RecordingDetail::where('recording_id', $recordingId)
            ->where('transfer_cloud', 1)
            ->whereNotNull('ticket_id')
            ->whereNotNull('linked_at')
            ->get();
    }
    
    /**
     * Check if a recording has ticket information
     * 
     * @param RecordingDetail $recording
     * @return bool
     */
    public function hasTicketInfo(RecordingDetail $recording): bool
    {
        return !empty($recording->ticket_id);
    }
}
