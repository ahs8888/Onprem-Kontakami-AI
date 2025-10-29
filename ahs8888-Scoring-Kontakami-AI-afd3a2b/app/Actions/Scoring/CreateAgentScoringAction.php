<?php
namespace App\Actions\Scoring;

use App\Enum\ProcessType;
use App\Helpers\Kontakami;
use App\Enum\ProcessStatus;
use App\Models\Account\User;
use Illuminate\Http\Request;
use App\Models\Util\ProcessLog;
use Illuminate\Support\Facades\DB;
use App\Models\Analisis\AgentScoring;
use App\Models\Analisis\AnalysisScoring;
use App\Models\Analisis\AgentScoringItem;
use Illuminate\Validation\ValidationException;
use App\Repository\Analisis\AnalysisRepository;

class CreateAgentScoringAction
{
     public function handle(Request $request, $userId)
     {
          // DB::transaction(function () use ($request, $userId, &$processUuid) {
          $now = now();
          $analysisId = id_from_uuid($request->analysis_uuid);

          // find still have progress analysis for dynamyc status
          $status = ProcessStatus::Progress;
          $activeProgress = AgentScoring::query()
               ->where('user_id', $userId)
               ->where('status', ProcessStatus::Progress)
               ->first();
          if ($activeProgress) {
               $status = ProcessStatus::Queue;
          }

          // create process log for floating popup progress


          $agentScoring = $this->createAgentScoring($request, $userId, $now);
          $process = ProcessLog::create([
               'user_id' => $userId,
               'status' => $status,
               'type' => ProcessType::AgentScoring,
               'data' => [
                    'analysis_id' => $analysisId,
                    'start_at' => now(),
                    'agent_scoring_id' => $agentScoring->id
               ]
          ]);
          DB::table('agent_scorings')
               ->where('id', $agentScoring->id)
               ->update([
                    'process_id' => $process->id
               ]);


          // });

          Kontakami::runInBackgroundArtisan('app:run-agent-scoring', $process->uuid);
     }
     public function createAgentScoring(Request $request, $userId, $now)
     {
          $analysisId = id_from_uuid($request->analysis_uuid);
          $filePath = $request->file('file')?->getRealPath();

          $analysis = (new AnalysisRepository())->findById($analysisId, $userId);
          $rows = $this->collectExcelRowData($filePath);
          $analysisScorings = AnalysisScoring::query()
               ->where('user_id', $userId)
               ->where('analysis_id', $analysisId)
               ->select([
                    'id',
                    'recording_file_id',
                    'analysis_id',
                    'filename',
                    'file_size',
                    'scoring',
                    'summary',
                    'sentiment'
               ])
               ->get();

          $analysisScorings = $analysisScorings->whereIn('filename', $rows->pluck('recording_name')->toArray())->values();


          $items = $this->collectAnalysisItem($analysisScorings, $rows);
          $scoringItems = $this->transformDataIntoSummaryCalculation($items, $analysis->version);


          $scoring = AgentScoring::create([
               'user_id' => $userId,
               'recording_id' => $analysis->recording_id,
               'analysis_id' => $analysis->id,
               'process_id' => 1,
               'foldername' => $analysis->foldername,
               'analysis_name' => $analysis->name,
               'total_data' => $scoringItems->count(),
               'start_at' => $now,
               'end_at' => null,
               'version' => 2
          ]);

          AgentScoringItem::insert(
               $scoringItems->map(fn($row) => [
                    'created_at' => $now,
                    'user_id' => $userId,
                    'agent_scoring_id' => $scoring->id,
                    'agent' => $row['agent'],
                    'spv' => $row['spv'],
                    'total_file' => $row['total_file'],
                    'avg_score' => $row['avg_score'],
                    'summary_temp' => json_encode($row['summary_temp']),
                    'scoring' => json_encode($row['scoring']),
                    'recording_files_id' => json_encode($row['recording_files_id']),
                    'analysis_scorings_id' => json_encode($row['analysis_scorings_id']),
                    'files' => json_encode($row['files']),
                    'token' => 0,
                    'version' => 2
               ])->toArray()
          );

          return $scoring;
     }

     private function transformDataIntoSummaryCalculation($items, $version)
     {
          // 1. Group by agent|spv
          $grouped = $items->groupBy(function ($item) {
               return $item['agent'] . '|' . $item['spv'];
          });

          // 2. Proses setiap group
          return $grouped->map(function ($items, $key) use ($version) {
               [$agent, $spv] = explode('|', $key);
               $total_data = $items->count(); // jumlah entri agent-spv

               // Title group => [item => ['true' => n, 'total' => n]]
               $scoringStats = [];
               if ($version == 1) {
                    foreach ($items as $entry) {
                         foreach ($entry['scoring'] as $score) {
                              $title = $score['title'];
                              foreach ($score['items'] as $titleItem => $scoreItem) {
                                   if (!isset($scoringStats[$title][$titleItem])) {
                                        $scoringStats[$title][$titleItem] = [
                                             'unit' => '',
                                             'max_score' => $score['total'],
                                             'total_score' => 0,
                                             'total' => 0,
                                        ];
                                   }

                                   $scoringStats[$title][$titleItem]['total'] += 1;
                                   $scoringStats[$title][$titleItem]['total_score'] += $scoreItem;
                              }
                         }
                    }
               } else {
                    foreach ($items as $entry) {
                         foreach ($entry['scoring'] as $score) {
                              $title = $score['title'];
                              foreach ($score['items'] as $row) {
                                   if (!isset($scoringStats[$title][$row['title']])) {
                                        $scoringStats[$title][$row['title']] = [
                                             'unit' => $row['unit'],
                                             'max_score' => $row['max_score'],
                                             'total_score' => 0,
                                             'total' => 0,
                                        ];
                                   }

                                   $scoringStats[$title][$row['title']]['total'] += 1;
                                   $scoringStats[$title][$row['title']]['total_score'] += $row['score'];
                              }
                         }
                    }
               }


               // Hitung persentase dan susun format akhir
               $scoring = collect($scoringStats)->map(function ($items, $title) {
                    $items = collect($items)->map(function ($row, $titleItem) {
                         $score = $row['total_score'] / $row['total'];
                         return [
                              'title' => $titleItem,
                              'score' => round($score, 0, PHP_ROUND_HALF_UP),
                              'max_score' => @$row['max_score'],
                              'unit' => @$row['unit'],
                         ];
                    })->values();

                    return [
                         'title' => $title,
                         'avg_score' => round($items->avg('score'), 0, PHP_ROUND_HALF_UP),
                         'items' => $items->toArray(),
                    ];
               })->values();

               $summary = $items->pluck('summary');
               $sentiment = $items->pluck('sentiment');
               $itemsSummary = [];
               if ($version == 1) {
                    $itemsSummary = collect($summary)
                         ->flatMap(function ($item) {
                              return [
                                   ['title' => $item['attitude_title'], 'result' => $item['attitude']],
                                   ['title' => $item['recomendation_title'], 'result' => $item['recomendation']]
                              ];
                         })
                         ->groupBy('title')
                         ->map(function ($group) {
                              $title = $group->first()['title'];
                              return [
                                   'title' => $title,
                                   'key' => str()->slug($title, '_'),
                                   'result' => $group->pluck('result')->implode(' ')
                              ];
                         })
                         ->values()
                         ->toArray();
               } else {
                    $itemsSummary = $summary->collapse()->groupBy('title')->map(function ($group) {
                         $title = $group->first()['title'];
                         return [
                              'title' => $title,
                              'key' => str()->slug($title, '_'),
                              'result' => $group->pluck('result')->implode(' ')
                         ];
                    })->values();
               }

               return [
                    'agent' => $agent,
                    'spv' => $spv,
                    'total_file' => $total_data,
                    'avg_score' => round($scoring->avg('avg_score'), 0, PHP_ROUND_HALF_UP),
                    'scoring' => $scoring->toArray(),
                    'recording_files_id' => $items->pluck('recording_file_id')->toArray(),
                    'analysis_scorings_id' => $items->pluck('id')->toArray(),
                    'files' => $items->pluck('filename')->toArray(),
                    'summary_temp' => [
                         'items' => $itemsSummary,
                         'sentiment' => $sentiment,
                    ]
               ];
          })->values();
     }

     private function collectAnalysisItem($analysisScorings, $rows)
     {
          $items = [];
          foreach ($analysisScorings as $analysis) {
               $findName = $rows->firstWhere('recording_name', $analysis['filename']);
               if (!$findName) {
                    continue;
               }

               $items[] = [
                    'agent' => $findName['nama_agent'],
                    'spv' => $findName['nama_spv'],
                    'recording_file_id' => $analysis['recording_file_id'],
                    'analysis_id' => $analysis['analysis_id'],
                    'filename' => $analysis['filename'],
                    'file_size' => $analysis['file_size'],
                    'summary' => $analysis['summary'],
                    'sentiment' => $analysis['sentiment'],
                    'scoring' => $analysis['scoring'],
                    'id' => $analysis['id'],
               ];
          }
          return collect($items);
     }
     private function collectExcelRowData($filePath)
     {

          if (($handle = fopen($filePath, 'r')) == false) {
               throw ValidationException::withMessages([
                    'file' => 'The uploaded file is invalid.'
               ]);
          }

          $rows = [];
          $header = array_map(function ($value) {
               return strtolower(preg_replace('/\s+/', '_', trim($value)));
          }, fgetcsv($handle, 1000, ';'));

          if (@$header[0] != 'nama_agent' || @$header[1] != 'nama_spv' || @$header[2] != 'recording_name') {
               throw ValidationException::withMessages([
                    'file' => 'The uploaded file is not in the expected format. Make sure the CSV file includes the correct headers'
               ]);
          }

          while (($data = fgetcsv($handle, 1000, ';')) !== false) {
               $rows[] = array_combine($header, $data);
          }


          fclose($handle);
          return collect($rows)->map(function ($row) {
               $row['recording_name'] = trim($row['recording_name']);
               return $row;
          });
     }
}