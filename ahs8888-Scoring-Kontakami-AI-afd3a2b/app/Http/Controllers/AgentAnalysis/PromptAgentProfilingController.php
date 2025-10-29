<?php

namespace App\Http\Controllers\AgentAnalysis;

use App\Http\Requests\RecordingAnalysis\PromptAnalysisRequest;
use App\Http\Resources\RecordingAnalysis\PromptAnalysisResource;
use Inertia\Inertia;
use App\Enum\PromptType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repository\Analisis\AnalisisPromptRepository;

class PromptAgentProfilingController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('agent-analysis/prompt/Index', [
            'guideline' => asset("files/Guidline Prompt.xlsx")
        ]);
    }

    public function choise(Request $request)
    {
        $choices = [
            [
                'type' => PromptType::AgentProfiling,
                'label' => PromptType::AgentProfiling->label(),
                'image' => asset('img/recording-scoring-sample-result.svg'),
                'description' => '[to be describe]'
            ]
        ];

        return Inertia::render('agent-analysis/prompt/add/Choise', [
            'choices' => $choices
        ]);
    }

    public function create(Request $request, $type)
    {
        return Inertia::render('agent-analysis/prompt/add/Index', [
            'type' => $type,
        ]);
    }

    public function edit(Request $request, $type, $uuid)
    {
        $item = (new AnalisisPromptRepository)->findByUuid($uuid, user()->id);
        return Inertia::render('agent-analysis/prompt/add/Index', [
            'type' => $type,
            'row' => $item
        ]);
    }
    public function store(PromptAnalysisRequest $request, $type)
    {
        (new AnalisisPromptRepository)->store($request, $type, user()->id);
        return to_route('setup.agent-analysis.prompt.index')->with(['success' => 'Prompt Successfully created !']);
    }


    public function destroy(Request $request, $uuid)
    {
        (new AnalisisPromptRepository)->deleteByUuid($uuid, user()->id);
        return response()->json('success');
    }

    public function datatable(Request $request)
    {
        $items = (new AnalisisPromptRepository)->datatable(
            $request,
            user()->id,
            [PromptType::AgentProfiling],
            $request->get('limit', 10)
        );
        return PromptAnalysisResource::collection($items);
    }

}
