<?php

namespace App\Console\Commands;

use App\Models\Data\Setting;
use App\Models\Data\Recording;
use App\Jobs\RetryUploadClouds;
use App\Services\CloudTransferService;
use Illuminate\Console\Command;
use App\Jobs\UpdateRecordingStatus;
use Illuminate\Support\Facades\Bus;
use App\Models\Data\RecordingDetail;
use Illuminate\Support\Facades\Http;

class UploadClouds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:upload-clouds {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recordingId = $this->argument("id");

        $token = Setting::where("key", "token")->value("value");
        $recording = Recording::where("id", $recordingId)->whereNotNull("clouds_uuid")->first();
        $this->info($recording ? "RECORD ADA" : "TIDAK ADA");

        if ($recording) {
            $this->info("RECORDING ".$recording->name);
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

            $this->info("ADA RECORD " . count($recIds));
            $this->info("ADA FILE " . count($files));

            $this->info("HIT TOKEN " . $token);

            if ($token) {
                $response = Http::withToken($token)
                    ->post(config("services.clouds_url")."/api/external/v1/recording/inject/".$recording->clouds_uuid, [
                        "files" => $files
                    ]);

                if ($response->successful()) {
                    $this->info("SUKSES HIT CLOUD");
                    RecordingDetail::whereIn("id", $recIds)->update([
                        "transfer_cloud" => 1
                    ]);
                } else {
                    $this->info("GAGAL HIT CLOUD");
                    \Log::warning("GAGAL HIT CLOUD", [
                        "status" => $response->status(),
                        "response" => $response,
                    ]);
                    Recording::where("id", $recordingId)->update([
                        'error_log' => $response->json()
                    ]);
                }

            }
        }
    }
}
