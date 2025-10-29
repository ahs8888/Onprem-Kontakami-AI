<?php
namespace App\Actions\RecordingFile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Recording\Recording;
use App\Actions\Analysis\RecordingScoringAction;
use App\Repository\Recording\RecordingRepository;

class CreateRecordingAnalysisAction
{
     public function handle(Request $request, $userId)
     {
          if ($request->auto) {
               Recording::query()
                    ->where('id', $request->recording_id)
                    ->where('user_id', $userId)
                    ->update([
                         'analisis_prompt_id' => $request->prompt_id
                    ]);
          }
          
          (new RecordingRepository)->readById($request->recording_id, $userId);
          (new RecordingScoringAction)->handle($request->prompt_id,$request->recording_id,$userId,$request->name);
     }
}