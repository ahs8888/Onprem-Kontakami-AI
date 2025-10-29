<?php
namespace App\Actions\Analysis;

use App\Helpers\Kontakami;
use App\Enum\ProcessStatus;
use App\Models\Util\ProcessLog;
use App\Models\Analisis\Analysis;
use App\Traits\BadRequestException;

class RetryFailedRecordingScoringAction
{
     public function handle($uuid, $userId)
     {
          $analysis = Analysis::query()
               ->where('id', id_from_uuid($uuid))
               ->where('user_id', $userId)
               ->firstOrFail();

          if ($analysis->status != ProcessStatus::Failed) {
               throw new BadRequestException('Analysis is not failed');
          }

          // find progress analysis
          $status = ProcessStatus::Queue;
          $haveOnProgress = Analysis::query()
               ->where('user_id', $userId)
               ->where('status', ProcessStatus::Progress)
               ->first();
          if (!$haveOnProgress) {
               $status = ProcessStatus::Progress;
               Kontakami::runInBackgroundArtisan('app:run-recording-scoring', id_to_uuid($analysis->process_id));
          }

          $analysis->update([
               'status' => $status
          ]);

          ProcessLog::query()
               ->where('id', $analysis->process_id)
               ->update([
                    'status' => $status
               ]);

     }
}