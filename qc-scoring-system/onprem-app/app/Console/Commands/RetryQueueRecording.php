<?php

namespace App\Console\Commands;

use App\Models\Data\Recording;
use Illuminate\Console\Command;
use App\Jobs\ProcessRecordingBatch;
use App\Jobs\UpdateRecordingStatus;

class RetryQueueRecording extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transcript:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transcript All Process';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recordings = Recording::where("status", "Queue")->orWhere("status", "Failed")->get();

        foreach ($recordings as $key => $value) {
            UpdateRecordingStatus::dispatch($value->id, "Progress");
            ProcessRecordingBatch::dispatch($value->id);
        }
    }
}
