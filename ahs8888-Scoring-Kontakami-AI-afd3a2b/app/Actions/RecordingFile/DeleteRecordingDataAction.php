<?php
namespace App\Actions\RecordingFile;

use App\Helpers\SocketBroadcast;
use App\Repository\Recording\RecordingRepository;

class DeleteRecordingDataAction
{
     public function handle($uuid, $userId)
     {
          (new RecordingRepository())->deleteByUuid($uuid, $userId);

          SocketBroadcast::channel('refresh-data')
               ->destination([$userId])
               ->send([
                    'type' => 'deleted_recording',
                    'id' => id_from_uuid($uuid),
               ]);
     }
}