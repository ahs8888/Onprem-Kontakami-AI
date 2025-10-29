<?php
namespace App\Actions\Scoring;

use ZipArchive;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Repository\Scoring\AgentScoringRepository;
use App\Actions\Scoring\ExportAgentScoringAnalysisAction;

class ExportAgentScoringItemAction
{
     public function execute(Request $request, $userId, $scoringUuid)
     {
          $agentScoring = (new AgentScoringRepository)->findByUuid($scoringUuid, $userId, [
               'items'
          ]);

          $zipPath = storage_path("app/private/analysis/Rekap-Agent-Scoring{$agentScoring->analysis_name}-{$agentScoring->foldername}.zip");
          $zip = new ZipArchive;

          if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
               foreach ($agentScoring->items as $item) {
                    $sheets = (new ExportAgentScoringAnalysisAction)->sheets($item);

                    $filename = "AgentScoring{$item->agent}_{$item->spv}.xlsx";
                    $path = "analysis/{$filename}";
                    $filePath = storage_path("app/private/{$path}");

                    (new FastExcel($sheets))
                         ->withoutHeaders()
                         ->configureOptionsUsing(function ($writer) {
                              $writer->DEFAULT_COLUMN_WIDTH = 90;
                         })->export($filePath);

                    $zip->addFromString($filename, Storage::get($path));
                    Storage::delete($path);
               }
               $zip->close();
          }

          return response()->download($zipPath, headers: [
               'filename' => "Rekap-Agent-Scoring{$agentScoring->analysis_name}-{$agentScoring->foldername}.zip",
          ])->deleteFileAfterSend(true);
     }
}