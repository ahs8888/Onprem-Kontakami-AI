<?php
namespace App\Actions\RecordingFile;

use Illuminate\Http\Request;
use App\Helpers\SocketBroadcast;
use Illuminate\Support\Facades\DB;
use App\Models\Recording\Recording;
use App\Traits\BadRequestException;
use App\Models\Recording\RecordingFile;
use App\Actions\Analysis\RecordingScoringAction;

class InjectRecordingFileAction
{
     public function handle(Request $request, $recordingId, $userId)
     {
          $recording = null;
          DB::transaction(function () use ($request, $recordingId, $userId,&$recording) {
               $recording = Recording::query()
                    ->where('user_id', $userId)
                    ->where('id', $recordingId)
                    ->first();
               if (!$recording) {
                    throw new BadRequestException('Recording not found');
               }

               $now = now();
               $files = collect($request->get('files'));
               $recording->update([
                    'total_file' => DB::raw('total_file + ' . $files->count()),
                    'total_token' => DB::raw('total_token + ' . $files->sum('token')),
                    'is_new' => true
               ]);

               $currentFile = RecordingFile::query()
                    ->where('recording_id', $recording->id)
                    ->pluck('id');

               RecordingFile::insert(
                    $files->map(fn($row) => [
                         'created_at' => $now,
                         'user_id' => $userId,
                         'recording_id' => $recording->id,
                         'filename' => $row['filename'],
                         'token' => $row['token'],
                         'size' => $row['size'],
                         'transcribe' => $row['transcribe'],
                    ])
                         ->toArray()
               );

               $allFile = RecordingFile::query()
                    ->where('recording_id', $recording->id)
                    ->pluck('id');
               $newFileId = $allFile->diff($currentFile)->values();

               if ($recording->analisis_prompt_id) {
                    (new RecordingScoringAction)->handle(
                         $recording->analisis_prompt_id,
                         $recording->id,
                         $userId,
                         filesId: $newFileId,
                    );
               }

               SocketBroadcast::channel('refresh-data')
                    ->destination([$userId])
                    ->send([
                         'type' => 'new_recording',
                         'id' => $recording->id
                    ]);
          });

          return $recording;
     }
}