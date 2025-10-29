<?php
namespace App\Actions\Scoring;

use App\Helpers\Kontakami;
use App\Enum\ProcessStatus;
use App\Models\Util\ProcessLog;
use App\Traits\BadRequestException;
use App\Models\Analisis\AgentScoring;

class RetryFailedAgentScoringAction
{
     public function handle($uuid, $userId)
     {
          $agentScoring = AgentScoring::query()
               ->where('id', id_from_uuid($uuid))
               ->where('user_id', $userId)
               ->firstOrFail();

          if ($agentScoring->status != ProcessStatus::Failed) {
               throw new BadRequestException('Agent Scoring is not failed');
          }

          // find progress analysis
          $status = ProcessStatus::Queue;
          $haveOnProgress = AgentScoring::query()
               ->where('user_id', $userId)
               ->where('status', ProcessStatus::Progress)
               ->first();
          if (!$haveOnProgress) {
               $status = ProcessStatus::Progress;
               Kontakami::runInBackgroundArtisan('app:run-agent-scoring', id_to_uuid($agentScoring->process_id));
          }

          $agentScoring->update([
               'status' => $status
          ]);

          ProcessLog::query()
               ->where('id', $agentScoring->process_id)
               ->update([
                    'status' => $status
               ]);
     }
}