<?php

namespace App\Http\Controllers\RecordingAnalysis;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Traits\BadRequestException;
use App\Http\Controllers\Controller;
use App\Actions\Scoring\CreateAgentScoringAction;
use App\Repository\Scoring\AgentScoringRepository;
use App\Actions\Scoring\ExportAgentScoringItemAction;
use App\Actions\Scoring\RetryFailedAgentScoringAction;
use App\Actions\Scoring\ExportAgentScoringAnalysisAction;
use App\Http\Resources\AgentScoring\AgentScoringResource;

class AgentScoringController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('recording-analysis/agent-scoring/Index');
    }

    public function create(Request $request)
    {
        return Inertia::render('recording-analysis/agent-scoring/add/Index', [
            'template' => asset('files/template_agent_scoring.csv')
        ]);
    }

    public function show(Request $request, $uuid)
    {
        $userId = user()->id;
        (new AgentScoringRepository)->readByUuid($uuid, $userId);

        $scoring = (new AgentScoringRepository)->findByUuid(
            $uuid,
            $userId,
            select: ['id', 'foldername', 'analysis_name', 'total_data', 'status']
        );

        return Inertia::render('recording-analysis/agent-scoring/Detail', [
            'scoring' => $scoring,
        ]);
    }
    public function scoring(Request $request, $scoringUuid, $itemUuid)
    {
        $userId = user()->id;
        $scoring = (new AgentScoringRepository)->findScoring($scoringUuid, $itemUuid, $userId);
        return Inertia::render($scoring->version==1 ? 'recording-analysis/agent-scoring/Scoring' : 'recording-analysis/agent-scoring/ScoringV2', [
            'scoring_uuid' => $scoringUuid,
            'item_uuid' => $itemUuid,
            'scoring' => $scoring
        ]);
    }

    public function analysis(Request $request, CreateAgentScoringAction $createAgentScoringAction)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'analysis_uuid' => 'required',
        ]);
        try {
            $createAgentScoringAction->handle($request, user()->id);
            return to_route('setup.recording-analysis.agent-scoring.index');
        } catch (BadRequestException $e) {
            return back()->with(['error' => $e->getMessage()]);
        }
        //  catch (\Exception $e) {
        //     return back()->with(['error' => $e->getMessage()]);
        // }
    }

    public function destroy(Request $request, $uuid)
    {
        (new AgentScoringRepository)->deleteByUuid($uuid, user()->id);
        return response()->json('success');
    }

    public function datatable(Request $request)
    {
        $items = (new AgentScoringRepository)->datatable($request, user()->id, $request->get('limit', 10));
        return AgentScoringResource::collection($items);
    }

    public function datatableItem(Request $request,$uuid){
        return (new AgentScoringRepository)->datatableItem($request,id_from_uuid($uuid), $request->get('limit', 10));
    }

    public function retry(Request $request, RetryFailedAgentScoringAction $retryFailedAgentScoringAction, $uuid)
    {
        try {
            $retryFailedAgentScoringAction->handle($uuid, user()->id);
            return to_route('setup.recording-analysis.agent-scoring.index');
        } catch (BadRequestException $e) {
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function exportScoring(Request $request, ExportAgentScoringItemAction $exportAgentScoringItemAction, $uuid)
    {
        return $exportAgentScoringItemAction->execute($request, user()->id, $uuid);
    }

    public function exportAnalysis(Request $request, ExportAgentScoringAnalysisAction $exportAgentScoringAnalysisAction, $scoringUuid, $itemUuid)
    {
        return $exportAgentScoringAnalysisAction->handle($request, user()->id, $scoringUuid, $itemUuid);
    }

}
