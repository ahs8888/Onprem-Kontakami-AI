<?php

namespace App\Http\Controllers\RecordingAnalysis;

use Inertia\Inertia;
use App\Enum\PromptType;
use Illuminate\Http\Request;
use App\Traits\BadRequestException;
use App\Http\Controllers\Controller;
use App\Repository\Recording\RecordingRepository;
use App\Actions\RecordingFile\StopAutoAnalysisAction;
use App\Repository\Analisis\AnalisisPromptRepository;
use App\Actions\RecordingFile\ExportRecordingFileAction;
use App\Actions\RecordingFile\CreateRecordingAnalysisAction;
use App\Actions\RecordingFile\ExportRecordingFileItemAction;
use App\Http\Resources\RecordingAnalysis\RecordingTextResource;
use App\Http\Resources\RecordingAnalysis\RecordingTextItemResource;

class RecordingTextController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('recording-analysis/recording-text/Index');
    }

    public function show(Request $request, $uuid)
    {
        $userId = user()->id;

        $recording = (new RecordingRepository)->findByUuid($uuid, $userId);
        return Inertia::render('recording-analysis/recording-text/Detail', [
            'recording' => $recording
        ]);

    }

    public function scoring(Request $request, $uuid)
    {
        $userId = user()->id;
        $recording = (new RecordingRepository)->findByUuid($uuid, $userId, select: ['id', 'folder']);
        if ($recording->in_use || $recording->analisis_prompt_id) {
            return back()->with(['error' => 'Recording text alreay in use']);
        }
        $prompts = (new AnalisisPromptRepository)->findAll($userId, [PromptType::RecordingScoring]);

        return Inertia::render('recording-analysis/recording-text/Scoring', [
            'prompts' => $prompts,
            'recording' => $recording
        ]);
    }

    public function addScoring(Request $request, CreateRecordingAnalysisAction $createRecordingAnalysisAction)
    {
        $request->validate([
            'prompt_id' => 'required',
            'recording_id' => 'required',
            'name' => 'required'
        ]);
        try {
            $createRecordingAnalysisAction->handle($request, user()->id);
            return to_route('setup.recording-analysis.recording-text.index');
        } catch (BadRequestException $e) {
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function stopScoring(Request $request, StopAutoAnalysisAction $stopAutoAnalysisAction, $uuid)
    {
        $stopAutoAnalysisAction->handle($uuid, user()->id);
        return response()->json('success');
    }

    public function datatable(Request $request)
    {
        $items = (new RecordingRepository)->datatable($request, user()->id, $request->get('limit', 10));
        return RecordingTextResource::collection($items);
    }

    public function datatableItem(Request $request, $recordingId)
    {
        $items =  (new RecordingRepository)->datatableFiles($request, id_from_uuid($recordingId), $request->get('limit', 10));
        return RecordingTextItemResource::collection($items);
    }

    public function destroy(Request $request, $uuid)
    {
        (new RecordingRepository)->deleteByUuid($uuid, user()->id);
        return response()->json('success');
    }

    public function export(Request $request, ExportRecordingFileAction $exportRecordingFileAction)
    {
        return $exportRecordingFileAction->execute($request, user()->id);
    }

    public function exportItem(Request $request, ExportRecordingFileItemAction $exportRecordingFileItemAction, $uuid)
    {
        return $exportRecordingFileItemAction->handle($uuid, user()->id);
    }
}
