<?php
namespace App\Actions\Analysis;

use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;
use App\Repository\Analisis\AnalysisRepository;

class ExportRecordingScoringAnalysisAction
{
     public function execute(Request $request, $userId, $analysisUuid, $scoringUuid)
     {

          $analysis = (new AnalysisRepository)->findScoring($analysisUuid, $scoringUuid, $userId);

          $sheets = $analysis->version == 1 ? $this->sheets($analysis) : $this->sheetsV2($analysis);

          $filename = "Recording Scoring [{$analysis->filename}].xlsx";
          return response()->streamDownload(function () use ($sheets, $filename) {
               (new FastExcel($sheets))
                    ->withoutHeaders()
                    ->configureOptionsUsing(function ($writer) {
                         $writer->DEFAULT_COLUMN_WIDTH = 70;
                    })
                    ->export('php://output');
          }, $filename, [
               'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
               'filename' => $filename,
               'Content-Disposition' => 'attachment; filename="' . $filename . '"'
          ]);
     }

     public function sheetsV2($analysis)
     {
          $scoring = collect($analysis->scoring)->flatMap(function ($section) {
               $rows = collect();
               $rows->push([$section['title'], ""]);
               $rows->push(["Criteria", "Nilai"]);
               collect($section['items'])->each(function ($item, $label) use ($rows) {
                    $maxScore = @$item['max_score'] ?: 5;
                    $unit = @$item['unit'];
                    $rows->push([
                         $item['title'],
                         "{$item['score']}{$unit} / {$maxScore}{$unit}"
                    ]);
               });
               return $rows;
          })->values();


          $nonScoring = collect($analysis->non_scoring)->flatMap(function ($section) {
               $rows = collect();
               $rows->push([$section['title'], ""]);
               $rows->push(["Criteria", "Nilai"]);
               collect($section['items'])->each(function ($item) use ($rows) {
                    $rows->push([
                         $item['title'],
                         @$item['value'] ? 'YA' : "TIDAK"
                    ]);
               });
               return $rows;
          })->values();
          if(!count($nonScoring)){
               $nonScoring = [
                    ['No Data','']
               ];
          }


          $conversations = collect($analysis->conversations)
               ->map(function ($row, $index) {
                    return [
                         $row['role'],
                         $row['text']
                    ];
               })->toArray();

          $summary = collect($analysis->summary)->map(function ($row) {
               return [
                    $row['title'],
                    $row['result']
               ];
          })->toArray();

          return new SheetCollection([
               'Transkrip' => [
                    ['Transkrip Panggilan', ''],
                    ...$conversations
               ],
               'Ringkasan' => [
                    [
                         'Contact Sentiment',
                         $analysis->sentiment,
                    ],
                    [
                         '',
                         ''
                    ],
                    [
                         'Criteria with Scoring',
                         ''
                    ],
                    ...$scoring,
                    [
                         '',
                         ''
                    ],
                    [
                         'Criteria without Scoring',
                         ''
                    ],
                    ...$nonScoring,
                    [
                         '',
                         ''
                    ],
                    [
                         'Summary',
                         ''
                    ],
                    ...$summary
               ]
          ]);
     }
     public function sheets($analysis)
     {
          $scoring = collect($analysis->scoring)->flatMap(function ($section) {
               $rows = collect();
               $rows->push([$section['title'], ""]);
               collect($section['items'])->each(function ($value, $label) use ($rows) {
                    $icon = $value ? '✅ ' : '❌ ';
                    $rows->push([$icon . $label, ""]);
               });
               $rows->push(["", ""]);
               return $rows;
          })->values();


          $nonScoring = collect($analysis->non_scoring)->flatMap(function ($section) {
               $rows = collect();
               $sectionRow = [$section['title'], ""];
               $rows->push($sectionRow);
               collect($section['items'])->each(function ($item) use ($rows) {
                    $rows->push([
                         $item['title'],
                         @$item['sentence'] ?: 'Tidak terdeteksi'
                    ]);
               });
               $rows->push(["", ""]);
               return $rows;
          })->values();

          // $scoringReport = collect($analysis->scoring)->map(function ($item) {
          //      return [
          //           $item['title'],
          //           "{$item['valid']}/{$item['total']} ({$item['percentage']}%)"
          //      ];
          // })->toArray();

          $conversations = collect($analysis->conversations)
               ->map(function ($row, $index) {
                    return [
                         $row['role'],
                         $row['text']
                    ];
               })->toArray();

          return new SheetCollection([
               'Transkrip' => [
                    ['Transkrip Panggilan', ''],
                    ...$conversations
               ],
               'Scoring' => [
                    ...$scoring,
                    ...$nonScoring,
                    // ['Kriteria Evaluasi', 'Hasil'],
                    // ...$scoringReport,
                    [
                         'Summary',
                         ''
                    ],
                    [
                         'Ringkasan Sikap Agent & Kualitas Pelayanan',
                         $analysis->summary['attitude']
                    ],
                    [
                         'Rekomendasi Perbaikan',
                         $analysis->summary['recomendation']
                    ],
                    [
                         '',
                         ''
                    ],
                    [
                         'Scoring Score',
                         round($analysis->avg_score) . '%'
                    ]
               ]
          ]);

     }
}