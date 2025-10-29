<?php
namespace App\Actions\Admin\RecordingList;

use Illuminate\Http\Request;
use App\Repository\Analisis\AdminAnalysisRecoringRepository;

class ExportRecordingListAction
{
     public function execute(Request $request)
     {
          $items = (new AdminAnalysisRecoringRepository)->datatable($request);
          $headers = [
               'Date',
               'Email Address',
               'Company Name',
               'Folder Name',
               'Total Data',
               'Total Token',
               'Action Type'
          ];

          header('Content-Type: text/csv');
          header("filename: AI Call Data Recording List.csv");
          header('Content-Disposition: attachment; filename="AI Call Data Recording List.csv"');
          $output = fopen('php://output', 'w');
          fputcsv($output, $headers, ';');

          foreach ($items as $row) {
               $data = [
                    $row->created_at,
                    $row->email,
                    $row->company,
                    $row->folder,
                    $row->data,
                    $row->token,
                    match ($row->type) {
                         'vtt' => 'Voice to text',
                         'rs' => 'Recording Scoring',
                         'as' => 'Agent Scoring',
                         default => '-'
                    }
               ];
               fputcsv($output, $data, ';');
          }

          fclose($output);
          exit;
     }
}