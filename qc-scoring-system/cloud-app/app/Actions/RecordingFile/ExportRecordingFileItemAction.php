<?php
namespace App\Actions\RecordingFile;

use ZipArchive;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\SheetCollection;
use App\Repository\Recording\RecordingRepository;

class ExportRecordingFileItemAction
{
     public function handle($recordingUuid, $userId)
     {
          $recording = (new RecordingRepository)->findByUuid($recordingUuid, $userId, [
               'files'
          ]);

          $zipPath = storage_path("app/private/analysis/recording-{$recording->folder}.zip");
          $zip = new ZipArchive;

          if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
               foreach ($recording->files as $item) {
                    $conversations = collect($item->conversations)
                         ->map(function ($row, $index) use ($item,$recording) {
                              return [
                                   $index == 0 ? $recording->created_at->format('Y-m-d') : '',
                                   $index == 0 ? $item->filename : '',
                                   $row['role'],
                                   $row['text']
                              ];
                         })->toArray();

                    $sheets = new SheetCollection([
                         substr($recording->folder,0,31) => [
                              ['Date','File Name', 'Text'],
                              ...$conversations
                         ]
                    ]);

                    $filename = "Recording Scoring [{$item->filename}].xlsx";
                    $path = "analysis/{$filename}";
                    $filePath = storage_path("app/private/{$path}");

                    (new FastExcel($sheets))
                         ->withoutHeaders()
                         ->configureOptionsUsing(function ($writer) {
                              $writer->DEFAULT_COLUMN_WIDTH = 20;
                         })->export($filePath);

                    $zip->addFromString($filename, Storage::get($path));
                    Storage::delete($path);
               }
               $zip->close();
          }

          return response()->download($zipPath, headers: [
               'filename' => "recording-{$recording->folder}.zip",
          ])->deleteFileAfterSend(true);
     }
}