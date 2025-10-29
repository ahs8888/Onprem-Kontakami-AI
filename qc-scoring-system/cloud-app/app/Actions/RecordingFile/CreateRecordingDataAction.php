<?php
namespace App\Actions\RecordingFile;

use App\Models\Recording\Recording;
use Illuminate\Http\Request;
use App\Helpers\SocketBroadcast;
use App\Repository\Recording\RecordingRepository;

class CreateRecordingDataAction
{
     public function handle(Request $request, $userID)
     {
          if ($request->boolean('is_ba_integration')) {
               $recording = Recording::where('user_id', $userID)->where('folder', $request->folder)->first();
               if ($recording) {
                    return (new InjectRecordingFileAction)->handle($request, $recording->id, $userID);
               }
          }
          $recording = (new RecordingRepository)->store($request, $userID);

          // // auto run analysis
          // $userSetting = (new UserSettingRepository)->find($userID);
          // if ($userSetting && $userSetting->prompt) {
          //      (new RecordingScoringAction)->handle($userSetting->prompt->id, $recording->id, $userID);
          // }
          SocketBroadcast::channel('refresh-data')
               ->destination([$userID])
               ->send([
                    'type' => 'new_recording',
                    'id' => $recording->id
               ]);

          return $recording;
     }
}