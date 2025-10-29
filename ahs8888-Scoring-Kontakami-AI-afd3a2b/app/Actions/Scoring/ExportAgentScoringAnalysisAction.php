<?php
namespace App\Actions\Scoring;

use App\Repository\Scoring\AgentScoringRepository;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;

class ExportAgentScoringAnalysisAction
{
     public function handle(Request $request, $userId, $scoringUuid, $itemUuid)
     {
          $agentScoring = (new AgentScoringRepository())->findScoring($scoringUuid, $itemUuid, $userId);
          $sheets = $agentScoring->version==1 ? $this->sheets($agentScoring) : $this->sheetsV2($agentScoring);
          $date = date('Y-m-d', strtotime($agentScoring->created_at));

          $filename = "AgentScoring{$agentScoring->agent}_{$agentScoring->spv}_{$date}.xlsx";
          return response()->streamDownload(function () use ($sheets, $filename) {
               (new FastExcel($sheets))
                    ->withoutHeaders()
                    ->configureOptionsUsing(function ($writer) {
                         $writer->DEFAULT_COLUMN_WIDTH = 90;
                    })
                    ->export('php://output');
          }, $filename, [
               'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
               'filename' => $filename,
               'Content-Disposition' => 'attachment; filename="' . $filename . '"'
          ]);
     }

     public function sheetsV2($agentScoring)
     {
          $scoring = collect($agentScoring->scoring)->flatMap(function ($section) {
               $rows = collect();
               $rows->push([$section['title'], ""]);
               // $rows->push(["Kriteria", "Nilai"]);
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

          $summary = collect($agentScoring->summary)->map(function ($row) {
               return [
                    $row['title'],
                    $row['result']
               ];
          })->toArray();

          return new SheetCollection([
               'Summary Nama Agent' => [
                    ['Kategori', 'Nilai / Rincian'],
                    ['Agent', $agentScoring->agent],
                    ['SPV', $agentScoring->spv],
                    ['Tanggal Proses', date('Y-m-d', strtotime($agentScoring->created_at))],
                    ['Total File Diproses', "{$agentScoring->total_file}"],
                    ['', ''],
                    ['Contact Sentiment', $agentScoring->sentiment],
                    ['', ''],
                    ['Criteria Scoring', ''],
                    ['Criteria', 'Nilai'],
                    ...$scoring,
                    ['', ''],
                    [
                         'Summary',
                         ''
                    ],
                    ...$summary,
                    [
                         'File Kontribusi',
                         implode(',',@$agentScoring->files ?: [])
                    ]
               ]
          ]);
     }
     public function sheets($agentScoring)
     {
          $scoring = collect($agentScoring->scoring);

          $scoring = $scoring->flatMap(function ($section) {
               $title = $section['title'];
               $avg = $section['avg_score'];
               $items = collect($section['items']);

               return collect([
                    ['', ''],
                    ["{$title} (Rata-Rata)%", "{$avg}%"],
                    ["Rincian {$title}", '']
               ])->merge(
                         $items->map(function ($item) {
                              return [$item['key'], "{$item['percentage']}%"];
                         })
                    );
          })->values()->toArray();

          return new SheetCollection([
               'Summary Nama Agent' => [
                    ['Kategori', 'Nilai / Rincian'],
                    ['Agent', $agentScoring->agent],
                    ['SPV', $agentScoring->spv],
                    ['Tanggal Proses', date('Y-m-d', strtotime($agentScoring->created_at))],
                    ['Total File Diproses', "{$agentScoring->total_file}"],
                    ['Total Skoring Keseluruhan (%)', "{$agentScoring->avg_score}%"],
                    ...$scoring,
                    ['', ''],
                    [
                         'Ringkasan Sikap Agent (AI)',
                         @$agentScoring->summary['attitude']
                    ],[
                         'Ringkasan Rekomendasi Perbaikan (AI)',
                         @$agentScoring->summary['recomendation']
                    ],[
                         'File Kontribusi',
                         implode(',',@$agentScoring->files ?: [])
                    ]
               ]
          ]);

     }
}