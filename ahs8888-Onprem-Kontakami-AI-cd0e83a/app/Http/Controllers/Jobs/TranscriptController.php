<?php

namespace App\Http\Controllers\Jobs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessRecordingDetail;
use App\Models\Data\Recording;
use App\Models\Data\RecordingDetail;

class TranscriptController extends Controller
{
    public function remainingTranscriptProcess()
    {
        $remaining = Recording::whereIn('status', ['Queue', 'Progress'])->count();
        return response()->json(['remaining' => $remaining]);
    }
}
