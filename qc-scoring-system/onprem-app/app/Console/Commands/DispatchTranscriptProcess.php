<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Data\RecordingDetail;
use App\Jobs\ProcessRecordingDetail;

class DispatchTranscriptProcess extends Command
{
    protected $signature = 'transcript:dispatch';
    protected $description = 'Dispatch jobs for transcript processing';

    public function handle()
    {
        $recordings = RecordingDetail::where('is_transcript', 0)->get();

        if ($recordings->isEmpty()) {
            logger('No recordings to process.');
            $this->info('No recordings to process.');
            return;
        }

        foreach ($recordings as $rec) {
            ProcessRecordingDetail::dispatch($rec);
        }

        logger("Dispatched " . $recordings->count() . " transcript jobs.");
        $this->info("Dispatched " . $recordings->count() . " transcript jobs.");
    }
}
