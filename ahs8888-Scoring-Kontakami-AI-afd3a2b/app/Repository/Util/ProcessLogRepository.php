<?php
namespace App\Repository\Util;

use App\Enum\ProcessStatus;
use App\Models\Util\ProcessLog;

class ProcessLogRepository
{
     public function findActiveProcess($userId)
     {
          return ProcessLog::query()
               ->where('user_id', $userId)
               ->whereIn('status', [ProcessStatus::Progress,ProcessStatus::Done])
               ->select(['id', 'progress', 'type','status'])
               ->get()
               ->map(function ($row) {
                    return [
                         'uuid' => $row->uuid,
                         'progress' => round($row->progress),
                         'type' => $row->type,
                         'status' => $row->status,
                         'label' => $row->type?->label(),
                    ];
               });
     }

}