<?php

namespace App\Http\Controllers\Util;

use App\Enum\ProcessStatus;
use Illuminate\Http\Request;
use App\Models\Util\ProcessLog;
use App\Helpers\SocketBroadcast;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Repository\Util\ProcessLogRepository;


class ProcessLogController extends Controller
{
    public function progress(Request $request)
    {
        $userId = user()->id;
        if (!$userId) {
            exit;
        }
        $cacheName = "last-active-process:{$userId}";

        $process = (new ProcessLogRepository)->findActiveProcess($userId);

        $totalProgress = $process->where('status', ProcessStatus::Progress->value)->count();

        $lastProgressProcess = intval(Cache::get($cacheName));
        if ($lastProgressProcess > $totalProgress) {
            SocketBroadcast::channel('refresh-data')
                ->destination([$userId])
                ->send([
                    'type' => 'process_done',
                ]);
        }
        Cache::put($cacheName, $totalProgress);

        return response()->json($process);
    }

    public function done(Request $request, $uuid)
    {
        $userId = user()->id;
        ProcessLog::query()
            ->where('id', id_from_uuid($uuid))
            ->where('user_id', $userId)
            ->where('status', ProcessStatus::Done)
            ->update([
                'status' => ProcessStatus::Finish
            ]);

        SocketBroadcast::channel('refresh-data')
            ->destination([$userId])
            ->send([
                'type' => 'process_done',
            ]);
        return response()->json('success');
    }
}
