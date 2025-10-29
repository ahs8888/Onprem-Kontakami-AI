<?php

namespace App\Console\Commands;

use App\Enum\ProcessType;
use App\Helpers\Kontakami;
use App\Enum\ProcessStatus;
use App\Helpers\KontakamiAI;
use App\Models\Account\User;
use App\Models\Util\ProcessLog;
use Illuminate\Console\Command;
use App\Models\Analisis\Analysis;
use Illuminate\Support\Facades\DB;
use App\Actions\Analysis\RetryFailedRecordingScoringAction;

class RunRecordingScoringCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-recording-scoring {process_uuid}';

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
        logger('Start Running Command app:run-recording-scoring');
        $processId = id_from_uuid($this->argument('process_uuid'));

        $process = ProcessLog::where('id', intval($processId))->first();
        if (!$process || !in_array($process?->status, [ProcessStatus::Progress, ProcessStatus::Failed])) {
            return;
        }

        $analysis = Analysis::where('id', @$process->data['analysis_id'])->first();
        if (!$analysis) {
            return;
        }
        $recordingId = $analysis->recording_id;

        $analysis->update([
            'process_id' => $process->id,
            'status' => ProcessStatus::Progress
        ]);
        DB::table('recordings')->where('id', $recordingId)->update(['in_use' => true]);

        try {

            $prompt = $analysis->prompt;
            $totalFiles = $analysis->scorings->where('is_done', 0)->count();

            $user = User::where('id', $analysis->user_id)->first();

            $ai = new KontakamiAI($user);
            $ai->version(@$prompt['version'] ?: 1);
            $ai->scoring($prompt['scorings']);
            $ai->nonScoring($prompt['non_scorings']);
            $ai->summary($prompt['version'] == 2 ? $prompt['summaries'] : $prompt['summary']);


            foreach ($analysis->scorings->where('is_done', 0)->values() as $index => $file) {
                // initial process progress by 50% ot item
                $process->update(['progress' => round((($index + 1) / $totalFiles * 100) / 2)]);

                // request to AI to analysis
                $analysisAI = $ai->analysisV2($file->transcribe);
                if (!$analysisAI) {
                    $this->failedProcess($process, $analysis, $recordingId, 'Cannot get output of AI analysys');
                    $this->runNextQueueAnalysis($process);
                    return;
                }


                $output = $analysisAI['output'];
                if (!$output) {
                    throw new \Exception('output empty');
                }
          
                $result = $this->transformScoringNonScoring($prompt, $output);

                $tokenUsage = $analysisAI['token'];
                $scoreTotal = collect($result['scorings'])->sum('score');
                $avgScore = $scoreTotal / count($result['scorings']);

                DB::table('analysis_scorings')
                    ->where('id', $file->id)
                    ->update([
                        'request_ai_id' => $analysisAI['request_ai_id'],
                        'scoring' => json_encode($result['scorings']),
                        'avg_score' => $avgScore,
                        'non_scoring' => json_encode($result['non_scorings']),
                        'sentiment' => $result['sentiment'],
                        'summary' => $result['summaries'],
                        'token' => $tokenUsage,
                        'is_done' => true
                    ]);


                DB::table('analysis')
                    ->where('id', $analysis->id)
                    ->update([
                        'total_token' => DB::raw("total_token + {$tokenUsage}")
                    ]);

                $process->update(['progress' => round(($index + 1) / $totalFiles * 100)]);
            }

        } catch (\Throwable $e) {
            logger('Failed run analysis recoring scoring ================================================================');
            logger($e);

            $this->failedProcess($process, $analysis, $recordingId, $e->getMessage());
            $this->runNextQueueAnalysis($process);

            return;
        }



        $now = now();
        $analysis->update([
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


        DB::table('recordings')->where('id', $recordingId)->update(['in_use' => false]);

        logger('End Running Command app:run-recording-scoring');
        $this->runNextQueueAnalysis($process);

    }

    private function transformScoringNonScoring($prompt, $output)
    {
        $scorings = collect($prompt['scorings'])->map(function ($row) use ($output) {
            $pointSlug = str()->slug($row['name'], '_');
            $items = collect($row['items'])->map(function ($row) use ($pointSlug, $output) {
                $titleSlug = str()->slug($row['point'], '_');
                $minScore = intval(@$row['score']);
                $maxScore = intval(@$row['score_max']);
                return [
                    'title' => $row['point'],
                    'score' => @$output->scoring->{$pointSlug}->{$titleSlug}->score ?: 0,
                    'min_score' => $maxScore<$minScore ? $maxScore : $minScore,
                    'max_score' => $minScore>$maxScore ? $minScore : $maxScore,
                    'unit' => @$row['unit']
                ];
            });
            $scoreTotal = $items->sum('score');

            return [
                'title' => $row['name'],
                'score' => $scoreTotal / count($items),
                'items' => $items->toArray()
            ];
        })->toArray();

        $nonScorings = collect($prompt['non_scorings'])->map(function ($row) use ($output) {
            $pointSlug = str()->slug($row['name'], '_');
            return [
                'title' => $row['name'],
                'items' => collect($row['items'])->map(function ($item) use ($pointSlug, $output) {
                    $itemSlug = str()->slug($item['point'], '_');
                    return [
                        'title' => $item['point'],
                        'value' => @$output->non_scoring->{$pointSlug}->{$itemSlug}->value ?: false
                    ];
                })->toArray(),
            ];
        })->toArray();

        $summaries = $this->summaryPromptParameter($prompt)->map(function ($row) use ($output) {
            $titleSlug = str()->slug($row['point'], '_');
            return [
                'title' => @$row['point'],
                'result' => @$output->summaries->{$titleSlug} ?: ''
            ];
        })->toArray();
        return [
            'scorings' => $scorings,
            'non_scorings' => $nonScorings,
            'summaries' => $summaries,
            'sentiment' => @$output->sentiment
        ];
    }
    private function runNextQueueAnalysis($process)
    {
        // start next analysis
        $nextProcess = ProcessLog::query()
            ->where('status', ProcessStatus::Queue)
            ->where('type', ProcessType::RecordingScoring)
            ->where('user_id', $process->user_id)
            ->where('id', '>', $process->id)
            ->first();
        if ($nextProcess) {
            $nextProcess->update([
                'status' => ProcessStatus::Progress
            ]);

            DB::table('analysis')
                ->where('id', @$nextProcess->data['analysis_id'])
                ->update([
                    'status' => ProcessStatus::Progress->value
                ]);

            logger("Running next recording scoring prosess : " . $nextProcess->id);
            Kontakami::runInBackgroundArtisan('app:run-recording-scoring', $nextProcess->uuid);
        } else {
            // Find failed process
            $failedProcess = ProcessLog::query()
                ->where('status', ProcessStatus::Failed)
                ->where('type', ProcessType::RecordingScoring)
                ->where('user_id', $process->user_id)
                ->orderBy('id', 'desc')
                ->first();
            if ($failedProcess) {
                (new RetryFailedRecordingScoringAction)->handle(id_to_uuid(@$failedProcess->data['analysis_id']), $process->user_id);
            }
        }
    }

    private function failedProcess($process, $analysis, $recordingId, $reason = null)
    {
        DB::table('recordings')->where('id', $recordingId)->update(['in_use' => false]);
        $process->update([
            'status' => ProcessStatus::Failed,
            'note' => $reason
        ]);
        $analysis->update([
            'status' => ProcessStatus::Failed
        ]);

    }

    private function summaryPromptParameter($prompt)
    {
        if ($prompt['version'] == 1) {
            return collect([
                [
                    'point' => @$prompt['summary']['attitude']['name'],
                    'prompt' => @$prompt['summary']['attitude']['prompt'],
                ],
                [
                    'point' => @$prompt['summary']['recomendation']['name'],
                    'prompt' => @$prompt['summary']['recomendation']['prompt'],
                ]
            ]);
        }

        return collect(@$prompt['summaries']);
    }
}
