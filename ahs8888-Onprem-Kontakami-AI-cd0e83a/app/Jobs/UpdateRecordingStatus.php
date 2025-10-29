<?php

namespace App\Jobs;

use App\Models\Data\Recording;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateRecordingStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $recordingId, $status;

    public function __construct($recordingId, $status)
    {
        $this->recordingId = $recordingId;
        $this->status = $status;
    }

    public function handle(): void
    {
        Recording::where('id', $this->recordingId)
            ->update(['status' => $this->status]);
    }
}
