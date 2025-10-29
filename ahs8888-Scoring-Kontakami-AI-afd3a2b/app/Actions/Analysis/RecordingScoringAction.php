<?php
namespace App\Actions\Analysis;

use App\Enum\ProcessType;
use App\Helpers\Kontakami;
use App\Enum\ProcessStatus;
use App\Models\Util\ProcessLog;
use App\Models\Analisis\Analysis;
use Illuminate\Support\Facades\DB;
use App\Models\Analisis\AnalysisScoring;
use App\Repository\Recording\RecordingRepository;
use App\Repository\Analisis\AnalisisPromptRepository;

class RecordingScoringAction
{
     public function handle($promptId, $recordingId, $userId, $name = null, $filesId = [])
     {

          // DB::transaction(function () use ($promptId, $recordingId, $userId, $name, $filesId,&$processUuid) {

          $now = now();
          $prompt = (new AnalisisPromptRepository)->findById($promptId, $userId);
          $recording = (new RecordingRepository)->findById($recordingId, $userId, ['files']);

          $analysisId = null;



          // find still have progress analysis for dynamyc status
          $status = ProcessStatus::Progress;
          $activeProgress = Analysis::query()
               ->where('user_id', $userId)
               ->where('status', ProcessStatus::Progress)
               ->first();
          if ($activeProgress) {
               $status = ProcessStatus::Queue;
          }
          if (!$name) {
               $name = "Analysis {$recording->folder}";
               // is edit file, put to current analysis cause is  auto
               $analysis = Analysis::query()
                    ->where('recording_id', $recordingId)
                    ->where('prompt_id', $promptId)
                    ->latest()
                    ->first();
               if ($analysis) {
                    $analysisId = $analysis->id;
               }
          }

          // create process log for floating popup progress
          $process = ProcessLog::create([
               'user_id' => $userId,
               'status' => $status,
               'type' => ProcessType::RecordingScoring,
               'data' => [
                    'prompt_id' => $promptId,
                    'recording_id' => $recordingId,
                    'start_at' => now()
               ]
          ]);


          $analysis = $this->createAnalysis($prompt, $recording, $userId, $now, $status, $process->id, $name, $filesId, $analysisId);

          $process->update([
               'data' => [
                    ...$process->data,
                    'analysis_id' => $analysis->id
               ]
          ]);
          DB::table('recordings')->where('id', $recordingId)->update(['in_use' => true]);
          // });
          Kontakami::runInBackgroundArtisan('app:run-recording-scoring', $process->uuid);
     }

     private function createAnalysis($prompt, $recording, $userId, $now, $status, $processId, $name, $filesId, $analysisId)
     {
          $files = $recording->files;
          if (count($filesId)) {
               $files = $files->whereIn('id', $filesId)->values();
          }

          $data = [
               'created_at' => $now,
               'user_id' => $userId,
               'prompt_id' => $prompt->id,
               'recording_id' => $recording->id,
               'foldername' => $recording->folder,
               'prompt_name' => $prompt->name,
               'prompt' => [
                    'type' => $prompt->type->value,
                    'scorings' => $prompt->scorings,
                    'non_scorings' => $prompt->non_scorings,
                    'summary' => $prompt->summary,
                    'summaries' => $prompt->summaries,
                    'version' => $prompt->version
               ],
               // 'total_token' => 0,
               'total_file' => DB::raw('total_file + ' . $files->count()),
               'end_at' => null,
               'process_id' => $processId,
               'name' => $name,
               'status' => $status,
               'version' => 2
          ];
          if (!$analysisId) {
               $data['start_at'] = $now;
          }
          $analysis = Analysis::updateOrCreate([
               'id' => $analysisId
          ], $data);
          AnalysisScoring::insert(
               $files->map(fn($row) => [
                    'created_at' => $now,
                    'user_id' => $userId,
                    'analysis_id' => $analysis->id,
                    'recording_file_id' => $row->id,
                    'filename' => $row->filename,
                    'file_size' => $row->size,
                    'avg_score' => 0,
                    'transcribe' => $row->transcribe,
                    'version' => 2,
                    'token' => 0
               ])->toArray()
          );

          return $analysis;
     }
}