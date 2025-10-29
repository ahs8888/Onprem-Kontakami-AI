<?php

namespace App\Console\Commands;

use App\Models\Data\Recording;
use App\Jobs\UpdateCloudTicketInfo;
use Illuminate\Console\Command;

class SyncTicketInfoToCloud extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticket:sync-to-cloud {recording_id?} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync ticket information to cloud for recordings already transferred';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recordingId = $this->argument('recording_id');
        $syncAll = $this->option('all');

        if ($recordingId) {
            // Sync specific recording folder
            $this->info("Syncing ticket info for recording: {$recordingId}");
            $recording = Recording::where('id', $recordingId)->whereNotNull('clouds_uuid')->first();
            
            if (!$recording) {
                $this->error("Recording not found or not transferred to cloud");
                return 1;
            }
            
            UpdateCloudTicketInfo::dispatch($recordingId);
            $this->info("Job dispatched successfully");
            
        } elseif ($syncAll) {
            // Sync all recordings that have cloud UUID
            $this->info("Finding recordings that need ticket sync...");
            
            $recordings = Recording::whereNotNull('clouds_uuid')
                ->whereHas('details', function($query) {
                    $query->where('transfer_cloud', 1)
                          ->whereNotNull('ticket_id');
                })
                ->get();
            
            if ($recordings->isEmpty()) {
                $this->info("No recordings need ticket sync");
                return 0;
            }
            
            $this->info("Found {$recordings->count()} recordings to sync");
            
            $bar = $this->output->createProgressBar($recordings->count());
            $bar->start();
            
            foreach ($recordings as $recording) {
                UpdateCloudTicketInfo::dispatch($recording->id);
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            $this->info("All sync jobs dispatched successfully");
            
        } else {
            $this->error("Please provide a recording ID or use --all flag");
            $this->info("Usage:");
            $this->info("  php artisan ticket:sync-to-cloud {recording_id}");
            $this->info("  php artisan ticket:sync-to-cloud --all");
            return 1;
        }

        return 0;
    }
}
