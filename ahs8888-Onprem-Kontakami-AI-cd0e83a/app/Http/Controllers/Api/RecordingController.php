<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Actions\Data\RecordingAction;

class RecordingController extends Controller
{
    public function storeFolder(Request $request, RecordingAction $recordingAction)
    {
        try {
            $request->validate([
                'folderName' => 'required',
                'files' => 'required|array',
                'files.*' => 'required|file|mimes:mp3,wav,gsm',
            ]);

            $recordingId = $recordingAction->storeFolder($request);

            return response()->json([
                "inject_id" => $recordingId
            ]);
        } catch (\Throwable $th) {
            $code = $th->getCode() == 0 || !$th->getCode() ? 400 : $th->getCode();
            return response()->json([
                'message' => $th->getMessage()
            ], $code);
        }
    }
}
