<?php

namespace App\Jobs;

use App\Models\Data\Setting;
use App\Models\Data\Recording;
use App\Models\Data\RecordingDetail;
use App\Services\CloudTransferService;
use Illuminate\Support\Facades\Http;
use App\Actions\Data\RecordingAction;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RetryRecording implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $recordingId;

    public function __construct($recordingId)
    {
        $this->recordingId = $recordingId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $token = Setting::where("key", "token")->value("value");
        $recordingId = $this->recordingId;
        $recording = Recording::where("id", $recordingId)->first();
        $cloudService = new CloudTransferService();

        if ($recording) {
            $recs = RecordingDetail::where("recording_id", $recordingId)->whereNotNull("error_log")->orderBy("sort", "asc")->get();
            $files = [];
            $recIds = [];

            foreach ($recs as $key => $value) {
                $relativePath = preg_replace('/^\/?storage\//', '', $value->file);
                $fullPath = storage_path("app/public/" . $relativePath);

                $responseTranscript = (new RecordingAction())->transcribeAudioGemini($relativePath);
                $transcript = $responseTranscript->json;
                // $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? 'No response text'
                if ($responseTranscript->status == 'success') {
                    $contentTranscript = $transcript['candidates'][0]['content']['parts'][0]['text'] ?? 'No response text';
                    $totalToken = $transcript['usageMetadata']['totalTokenCount'];

                    // Use CloudTransferService to prepare data with ticket info
                    $value->transcript = $contentTranscript;
                    $value->token = $totalToken;
                    array_push($files, $cloudService->prepareRecordingData($value, $totalToken));

                    array_push($recIds, $value->id);

                    $value->update([
                        'is_transcript' => 1,
                        'transcript' => $contentTranscript,
                        'token' => $totalToken,
                        'error_log' => null,
                        'status' => "Success"
                    ]);

                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                } else {
                    $value->update([
                        'is_transcript' => 1,
                        'error_log' => $transcript,
                        'status' => 'Failed'
                    ]);
                }
            }

            Recording::where("id", $recordingId)->update([
                'status' => "Done"
            ]);

            if ($token) {
                if (!$recording->clouds_uuid) {
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
