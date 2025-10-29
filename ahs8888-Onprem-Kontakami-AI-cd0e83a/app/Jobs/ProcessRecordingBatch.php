<?php

namespace App\Jobs;

use App\Models\Data\Setting;
use App\Models\Data\Recording;
use App\Models\Data\RecordingDetail;
use App\Services\CloudTransferService;
use App\Services\TranscriptCleaningService;
use App\Services\AIFilterService;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Actions\Data\RecordingAction;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessRecordingBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $recordingId, $injectId;

    public function __construct($recordingId, $injectId = null)
    {
        $this->recordingId = $recordingId;
        $this->injectId = $injectId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $token = Setting::where("key", "token")->value("value");
        $recordingId = $this->recordingId;
        $recording = Recording::where("id", $recordingId)->first();
        $injectId = $this->injectId;
        
        // Initialize AI services
        $cloudService = new CloudTransferService();
        $cleaningService = new TranscriptCleaningService();
        $aiFilter = new AIFilterService($cleaningService);
        $encryptionService = new EncryptionService();

        if ($recording) {
            $recs = RecordingDetail::where("recording_id", $recordingId)->where("is_transcript", 0)->orderBy("sort", "asc")->get();
            $files = [];
            $recIds = [];
            $approvedForUpload = [];

            foreach ($recs as $key => $value) {
                $relativePath = preg_replace('/^\/?storage\//', '', $value->file);
                $fullPath = storage_path("app/public/" . $relativePath);

                // Perform STT
                $responseTranscript = (new RecordingAction())->transcribeAudioGemini($relativePath);
                $transcript = $responseTranscript->json;
                
                if ($responseTranscript->status == 'success') {
                    $rawTranscript = $transcript['candidates'][0]['content']['parts'][0]['text'] ?? 'No response text';
                    $totalToken = $transcript['usageMetadata']['totalTokenCount'];

                    // ============================================
                    // LAYER 1: CLEAN TRANSCRIPT
                    // ============================================
                    $cleanedData = $cleaningService->cleanTranscript($rawTranscript);
                    
                    Log::info("Layer 1 - Transcript cleaned", [
                        'recording' => $value->name,
                        'confidence' => $cleanedData['confidence_score'],
                        'filler_removed' => $cleanedData['filler_words_removed'],
                        'noise_removed' => $cleanedData['noise_tags_removed']
                    ]);

                    // ============================================
                    // AI MICRO-DECISION: QUALITY EVALUATION
                    // ============================================
                    $fileSize = file_exists($fullPath) ? filesize($fullPath) : 0;
                    $duration = 120; // TODO: Get actual duration from audio file
                    
                    $decision = $aiFilter->evaluateRecording([
                        'transcript' => $rawTranscript,
                        'duration' => $duration,
                        'file_size' => $fileSize
                    ]);
                    
                    Log::info("AI Filter Decision", [
                        'recording' => $value->name,
                        'should_upload' => $decision->shouldUpload,
                        'reason' => $decision->reason,
                        'confidence' => $decision->confidence_score,
                        'quality' => $decision->quality_score,
                        'pii_detected' => $decision->contains_pii,
                        'action' => $decision->recommended_action
                    ]);

                    // Update recording with AI scores
                    $value->update([
                        'is_transcript' => 1,
                        'transcript' => $decision->cleaned_transcript, // Use cleaned version
                        'token' => $totalToken,
                        'confidence_score' => $decision->confidence_score,
                        'quality_score' => $decision->quality_score,
                        'pii_detected' => $decision->contains_pii,
                        'pii_types' => $decision->pii_detected,
                        'upload_decision' => $decision->shouldUpload ? 'approved' : 'rejected',
                        'decision_reason' => $decision->reason,
                        'cleaning_metadata' => $decision->metadata,
                        'processed_at' => now(),
                        'original_size_bytes' => $fileSize,
                        'error_log' => null,
                        'status' => $decision->shouldUpload ? 'Success' : 'Filtered'
                    ]);

                    // ============================================
                    // SELECTIVE UPLOAD: Only upload if approved
                    // ============================================
                    if ($decision->shouldUpload) {
                        // ============================================
                        // ENCRYPTION: Encrypt file before upload
                        // ============================================
                        $encryptedPath = $fullPath;
                        
                        if (config('ai_filter.enable_encryption', true)) {
                            $encryptResult = $encryptionService->compressAndEncrypt($fullPath);
                            
                            if ($encryptResult['success']) {
                                $encryptedPath = $encryptResult['encrypted_path'];
                                
                                Log::info("File encrypted and compressed", [
                                    'recording' => $value->name,
                                    'original_size' => $encryptResult['original_size'],
                                    'encrypted_size' => $encryptResult['encrypted_size'],
                                    'compression_ratio' => $encryptResult['compression_ratio'] ?? 0
                                ]);
                                
                                // Update encryption metadata
                                $value->update([
                                    'is_encrypted' => true,
                                    'encryption_algorithm' => $encryptResult['algorithm'],
                                    'encrypted_size_bytes' => $encryptResult['encrypted_size']
                                ]);
                            }
                        }

                        // Prepare for cloud upload
                        $value->transcript = $decision->cleaned_transcript;
                        $value->token = $totalToken;
                        array_push($files, $cloudService->prepareRecordingData($value, $totalToken));
                        array_push($recIds, $value->id);
                        array_push($approvedForUpload, $value->id);

                        // Delete local file after encryption (optional)
                        if (config('ai_filter.delete_after_encrypt', false)) {
                            if (file_exists($fullPath)) {
                                @unlink($fullPath);
                            }
                        }
                    } else {
                        // Recording rejected by AI filter
                        Log::warning("Recording rejected by AI filter", [
                            'recording' => $value->name,
                            'reason' => $decision->reason,
                            'action' => $decision->recommended_action
                        ]);
                        
                        // Handle retry logic
                        if ($aiFilter->shouldRetry($decision, $value->retry_count)) {
                            $value->update([
                                'retry_count' => $value->retry_count + 1,
                                'last_retry_at' => now()
                            ]);
                            
                            Log::info("Scheduling retry for recording", [
                                'recording' => $value->name,
                                'retry_count' => $value->retry_count + 1
                            ]);
                            
                            // Re-queue for processing (optional)
                            // ProcessRecordingBatch::dispatch($recordingId, $injectId)->delay(now()->addMinutes(5));
                        }
                    }

                } else {
                    // STT failed
                    $value->update([
                        'is_transcript' => 1,
                        'error_log' => $transcript,
                        'status' => 'Failed',
                        'upload_decision' => 'rejected',
                        'decision_reason' => 'STT processing failed'
                    ]);
                }

                // Rate limiting
                if (($key + 1) % 50 == 0) {
                    sleep(3);
                }
            }

            Recording::where("id", $recordingId)->update([
                'status' => "Done"
            ]);

            // ============================================
            // CLOUD TRANSFER: Only approved recordings
            // ============================================
            Log::info("Cloud transfer summary", [
                'total_processed' => count($recs),
                'approved_for_upload' => count($approvedForUpload),
                'filter_rate' => count($recs) > 0 ? round((count($recs) - count($approvedForUpload)) / count($recs) * 100, 2) . '%' : '0%'
            ]);

            if ($token && !empty($files)) {
                if (!$this->injectId || !$recording->clouds_uuid) {
                    $response = Http::withToken($token)
                        ->post(config("services.clouds_url")."/api/external/v1/recording", [
                            "folder" => $recording->folder_name,
                            "files" => $files
                        ]);

                    if ($response->successful()) {
                        $cloudId = @$response->json()['data']['uuid'];
                        Recording::where("id", $recordingId)->update([
                            'clouds_uuid' => $cloudId
                        ]);

                        RecordingDetail::whereIn("id", $recIds)->update([
                            "transfer_cloud" => 1
                        ]);
                    } else {
                        Recording::where("id", $recordingId)->update([
                            'error_log' => $response->json()
                        ]);
                    }
                } else {
                    $response = Http::withToken($token)
                        ->post(config("services.clouds_url")."/api/external/v1/recording/inject/".$recording->clouds_uuid, [
                            "files" => $files
                        ]);

                    if ($response->successful()) {
                        RecordingDetail::whereIn("id", $recIds)->update([
                            "transfer_cloud" => 1
                        ]);
                    } else {
                        Recording::where("id", $recordingId)->update([
                            'error_log' => $response->json()
                        ]);
                    }
                }

            }
        }


    }
}
