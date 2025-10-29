<?php
namespace App\Actions\Analysis;

use ZipArchive;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Storage;
use App\Repository\Analisis\AnalysisRepository;
use App\Actions\Analysis\ExportRecordingScoringAnalysisAction;

class ExportRecordingScoringItemAction
{
     public function execute(Request $request, $userId, $analysisUuid)
     {
          $analysis = (new AnalysisRepository)->findByUuid($analysisUuid, $userId, [
               'scorings'
          ]);

          $zipPath = storage_path("app/private/analysis/{$analysis->name}-{$analysis->foldername}-{$analysis->prompt_name}.zip");
          $zip = new ZipArchive;

          if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
               foreach ($analysis->scorings as $item) {
                    $sheets = $analysis->vesion==1 ? (new ExportRecordingScoringAnalysisAction)->sheets($item) : (new ExportRecordingScoringAnalysisAction)->sheetsV2($item);

                    $filename = "Recording Scoring [{$item->filename}].xlsx";
                    $path = "analysis/{$filename}";
                    $filePath = storage_path("app/private/{$path}");

                    (new FastExcel($sheets))
                         ->withoutHeaders()
                         ->configureOptionsUsing(function ($writer) {
                              $writer->DEFAULT_COLUMN_WIDTH = 70;
                         })->export($filePath);

                    $zip->addFromString($filename, Storage::get($path));
                    Storage::delete($path);
               }
               $zip->close();
          }

          return response()->download($zipPath,headers : [
               'filename' => "{$analysis->name}-{$analysis->foldername}-{$analysis->prompt_name}.zip",
          ])->deleteFileAfterSend(true);
     }
}