<?php

namespace App\Http\Controllers\RecordingAnalysis;

use Inertia\Inertia;
use App\Enum\PromptType;
use App\Enum\ProcessStatus;
use Illuminate\Http\Request;
use App\Traits\BadRequestException;
use App\Http\Controllers\Controller;
use App\Repository\Util\UserSettingRepository;
use App\Actions\Analysis\SetAutoAnalysisAction;
use App\Repository\Analisis\AnalysisRepository;
use App\Actions\Analysis\RecordingScoringAction;
use App\Repository\Recording\RecordingRepository;
use App\Repository\Analisis\AnalisisPromptRepository;
use App\Actions\Analysis\ExportRecordingScoringAction;
use App\Actions\Analysis\ExportRecordingScoringItemAction;
use App\Http\Resources\RecordingAnalysis\AnalysisResource;
use App\Actions\Analysis\RetryFailedRecordingScoringAction;
use App\Actions\Analysis\ExportRecordingScoringAnalysisAction;
use App\Http\Resources\RecordingAnalysis\AnalysisItemResource;

class RecordingScoringController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('recording-analysis/recording-scoring/Index');
    }

    public function create(Request $request)
    {
        $userId = user()->id;
        $prompts = (new AnalisisPromptRepository)->findAll($userId, [PromptType::RecordingScoring]);
        $recordings = (new RecordingRepository)->findAll($userId);

        return Inertia::render('recording-analysis/recording-scoring/Create', [
            'prompts' => $prompts,
            'recordings' => $recordings,
        ]);
    }

    public function show(Request $request, $uuid)
    {
        $userId = user()->id;
        (new AnalysisRepository)->readByUuid($uuid, $userId);

        $recording = (new AnalysisRepository)->findByUuid($uuid, $userId);

        return Inertia::render('recording-analysis/recording-scoring/Detail', [
            'recording' => $recording,
        ]);
    }

    public function analysis(Request $request, $analysisUuid, $scoringUuid)
    {
        $userId = user()->id;
        $scoring = (new AnalysisRepository)->findScoring($analysisUuid, $scoringUuid, $userId);
        return Inertia::render($scoring->version == 1 ? 'recording-analysis/recording-scoring/Analysis' : 'recording-analysis/recording-scoring/AnalysisV2', [
            'analysis_uuid' => $analysisUuid,
            'scoring_uuid' => $scoringUuid,
            'scoring' => $scoring
        ]);
    }

    public function store(Request $request, RecordingScoringAction $recordingScoringAction)
    {
        $request->validate([
            'prompt_id' => 'required',
            'recording_id' => 'required'
        ]);
        try {
            $recordingScoringAction->handle($request->prompt_id, $request->recording_id, user()->id);
            return to_route('setup.recording-analysis.recording-scoring.index');
        } catch (BadRequestException $e) {
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Request $request, $uuid)
    {
        (new AnalysisRepository)->deleteByUuid($uuid, user()->id);
        return response()->json('success');
    }

    public function datatable(Request $request)
    {
        $items = (new AnalysisRepository)->datatable($request, user()->id, $request->get('limit', 10));
        return AnalysisResource::collection($items);
    }

    public function datatableItem(Request $request, $uuid)
    {
        $items = (new AnalysisRepository)->datatableItem($request, id_from_uuid($uuid), $request->get('limit', 10));
        return AnalysisItemResource::collection($items);
    }

    public function export(Request $request, ExportRecordingScoringAction $exportRecordingScoringAction)
    {
        return $exportRecordingScoringAction->execute($request, user()->id);
    }

    public function exportScoring(Request $request, ExportRecordingScoringItemAction $exportRecordingScoringItemAction, $uuid)
    {
        return $exportRecordingScoringItemAction->execute($request, user()->id, $uuid);
    }

    public function exportAnalysis(Request $request, ExportRecordingScoringAnalysisAction $exportRecordingScoringAnalysisAction, $analysisUuid, $scoringUuid)
    {
        return $exportRecordingScoringAnalysisAction->execute($request, user()->id, $analysisUuid, $scoringUuid);
    }

    public function retry(Request $request, RetryFailedRecordingScoringAction $retryFailedRecordingScoringAction, $uuid)
    {
        try {
            $retryFailedRecordingScoringAction->handle($uuid, user()->id);
            return Inertia::location(route('setup.recording-analysis.recording-scoring.index'));
        } catch (BadRequestException $e) {
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function autoAnalysis(Request $request, SetAutoAnalysisAction $setAutoAnalysisAction)
    {
        $setAutoAnalysisAction->handle($request, user()->id);
        return to_route('setup.recording-analysis.recording-scoring.index')->with(['success' => 'Successfully !']);
    }
}
