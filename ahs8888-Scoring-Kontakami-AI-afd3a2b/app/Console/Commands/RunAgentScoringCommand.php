<?php

namespace App\Console\Commands;

use App\Enum\ProcessType;
use App\Helpers\Kontakami;
use App\Enum\ProcessStatus;
use App\Helpers\KontakamiAI;
use App\Models\Account\User;
use App\Models\Util\ProcessLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Analisis\AgentScoring;
use App\Actions\Scoring\RetryFailedAgentScoringAction;

class RunAgentScoringCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-agent-scoring {process_uuid}';

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
        logger('Start Running Command app:run-agent-scoring');
        $processUuid = $this->argument('process_uuid');

        $process = ProcessLog::where('id', id_from_uuid($processUuid))->first();
        if (!$process || !in_array($process?->status, [ProcessStatus::Progress, ProcessStatus::Failed])) {
            return;
        }

        $agentScoring = AgentScoring::where('id', @$process->data['agent_scoring_id'])->first();
        if (!$agentScoring) {
            return;
        }

        $agentScoring->update([
            'process_id' => $process->id,
            'status' => ProcessStatus::Progress
        ]);


        try {
            $totalitems = $agentScoring->items->where('is_done', 0)->count();
            $user = User::where('id', $agentScoring->user_id)->first();

            $ai = new KontakamiAI($user);

            foreach ($agentScoring->items->where('is_done', 0)->values() as $index => $item) {
                // initial process progress by 50% ot item
                $process->update(['progress' => round((($index + 1) / $totalitems * 100) / 2)]);

                // request to AI to analysis
                $summaryTemp = $item->summary_temp;
                $analysisAI = $ai->agentScoringV2($summaryTemp);

                if (!$analysisAI) {
                    $this->failedProcess($process, $agentScoring, 'Cannot get output of AI analysys');
                    $this->runNextQueueAnalysis($process);
                    return;
                }


                $output = $analysisAI['output'];
                $tokenUsage = $analysisAI['token'];
                $output = (array) $output;

                $summary = collect($summaryTemp['items'])->map(function ($row) use ($output) {
                    return [
                        'title' => @$row['title'],
                        'result' => @$output['summaries']->{$row['key']} ?: ''
                    ];
                })->toArray();

                DB::table('agent_scoring_items')
                    ->where('id', $item->id)
                    ->update([
                        'request_ai_id' => $analysisAI['request_ai_id'],
                        'summary' => json_encode($summary),
                        'sentiment' => $output['sentiment'],
                        'token' => $tokenUsage,
                        'is_done' => true
                    ]);


                DB::table('agent_scorings')
                    ->where('id', $agentScoring->id)
                    ->update([
                        'total_token' => DB::raw("total_token + {$tokenUsage}")
                    ]);

                $process->update(['progress' => round(($index + 1) / $totalitems * 100)]);
            }

        } catch (\Throwable $e) {
            logger('Failed run analysis agent scoring ================================================================');
            logger($e);

            $this->failedProcess($process, $agentScoring, $e->getMessage());
            $this->runNextQueueAnalysis($process);

            return;
        }

        $now = now();
        $agentScoring->update([
            'end_at' => $now,
            'status' => ProcessStatus::Done
        ]);

        $process->update([
            'progress' => 100,
            'status' => ProcessStatus::Done,
            'note' => null,
            'data' => [
                ...$process->data,
                'end_at' => $now,
            ]
        ]);


        logger('End Running Command app:run-agent-scoring');
        $this->runNextQueueAnalysis($process);
    }

    private function runNextQueueAnalysis($process)
    {
        // start next analysis
        $nextProcess = ProcessLog::query()
            ->where('status', ProcessStatus::Queue)
            ->where('type', ProcessType::AgentScoring)
            ->where('user_id', $process->user_id)
            ->where('id', '>', $process->id)
            ->first();
        if ($nextProcess) {
            $nextProcess->update([
                'status' => ProcessStatus::Progress
            ]);

            DB::table('agent_scorings')
                ->where('id', @$nextProcess->data['agent_scoring_id'])
                ->update([
                    'status' => ProcessStatus::Progress->value
                ]);

            logger("Running next agent scoring prosess : " . $nextProcess->id);
            Kontakami::runInBackgroundArtisan('app:run-agent-scoring', $nextProcess->uuid);
        } else {
            // Find failed process
            $failedProcess = ProcessLog::query()
                ->where('status', ProcessStatus::Failed)
                ->where('type', ProcessType::AgentScoring)
                ->where('user_id', $process->user_id)
                ->orderBy('id', 'desc')
                ->first();
            if ($failedProcess) {
                (new RetryFailedAgentScoringAction)->handle(id_to_uuid(@$failedProcess->data['agent_scoring_id']), $process->user_id);
            }
        }
    }

    private function failedProcess($process, $agentScoring, $reason = null)
    {
        $process->update([
            'status' => ProcessStatus::Failed,
            'note' => $reason
        ]);
        $agentScoring->update([
            'status' => ProcessStatus::Failed
        ]);

    }
}
