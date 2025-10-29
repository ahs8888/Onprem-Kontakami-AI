<?php
namespace App\Actions\Analysis;

use Illuminate\Http\Request;
use App\Repository\Analisis\AnalysisRepository;

class ExportRecordingScoringAction
{
     public function execute(Request $request, $userId)
     {
          $items = (new AnalysisRepository())->datatable($request, $userId);
          $headers = [
               'Date',
               'Folder Name',
               'Prompt',
               'Total Data'
          ];

          header('Content-Type: text/csv');
          header("filename: Recoding Analysis.csv");
          header('Content-Disposition: attachment; filename="Recoding Analysis.csv"');
          $output = fopen('php://output', 'w');
          fputcsv($output, $headers, ';');

          foreach ($items as $row) {
               $data = [
                    $row->created_at->format('d-M-Y H:i'),
                    $row->foldername,
                    $row->prompt_name,
                    $row->total_file,
               ];
               fputcsv($output, $data, ';');
          }

          fclose($output);
          exit;
     }
}