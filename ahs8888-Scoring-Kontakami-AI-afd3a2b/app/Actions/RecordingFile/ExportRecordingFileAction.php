<?php
namespace App\Actions\RecordingFile;

use App\Repository\Recording\RecordingRepository;
use Illuminate\Http\Request;

class ExportRecordingFileAction
{
     public function execute(Request $request, $userId)
     {
          $items = (new RecordingRepository())->datatable($request, $userId);
          $headers = [
               'Date',
               'Folder Name',
               'Total Data'
          ];

          header('Content-Type: text/csv');
          header("filename: Recoding Text.csv");
          header('Content-Disposition: attachment; filename="Recoding Text.csv"');
          $output = fopen('php://output', 'w');
          fputcsv($output, $headers, ';');

          foreach ($items as $row) {
               $data = [
                    $row->created_at->format('d-M-Y H:i'),
                    $row->folder,
                    $row->total_file,
               ];
               fputcsv($output, $data, ';');
          }

          fclose($output);
          exit;
     }
}