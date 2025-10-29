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

class RetryUploadClouds implements ShouldQueue
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
        $recording = Recording::where("id", $recordingId)->whereNotNull("clouds_uuid")->first();

        if ($recording) {
            $recs = RecordingDetail::where("recording_id", $recordingId)->where("status", "Success")->where("transfer_cloud", 0)->orderBy("sort", "asc")->get();
            $files = [];
            $recIds = [];

            foreach ($recs as $key => $value) {
                $relativePath = preg_replace('/^\/?storage\//', '', $value->file);
                $fullPath = storage_path("app/public/" . $relativePath);
                $sizeInBytes = file_exists($fullPath) ? filesize($fullPath) : 0;
                $sizeFormatted = formatBytes($sizeInBytes);
                array_push($recIds, $value->id);
                array_push($files, [
                    'filename' => $value->name,
                    'size' => $sizeFormatted,
                    'token' => 0,
                    'transcribe' => $value->transcript
                ]);
            }

            Recording::where("id", $recordingId)->update([
                'status' => "Done"
            ]);

            if ($token) {
                $response = Http::withToken($token)
                    ->post(config("services.clouds_url")."/api/external/v1/recording/inject/",$recording->clouds_uuid, [
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
