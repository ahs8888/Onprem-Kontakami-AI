<?php
namespace App\Actions\RecordingFile;

use App\Models\Recording\Recording;

class StopAutoAnalysisAction
{
     public function handle($uuid, $userId)
     {
          Recording::query()
               ->where('user_id', $userId)
               ->where('id', id_from_uuid($uuid))
               ->update([
                    'analisis_prompt_id' => null
               ]);
     }
}