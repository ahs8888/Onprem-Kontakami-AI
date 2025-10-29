<?php

namespace App\Http\Controllers\RecordingAnalysis;

use Inertia\Inertia;
use App\Enum\PromptType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repository\Account\UserRepository;
use App\Repository\Analisis\AnalisisPromptRepository;
use App\Http\Requests\RecordingAnalysis\PromptAnalysisRequest;
use App\Http\Resources\RecordingAnalysis\PromptAnalysisResource;

class PromptRecordingAnalysisController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('recording-analysis/prompt/Index', [
            // 'guideline' => asset("files/Guidline Prompt.xlsx"),
            'is_admin' => (bool) admin()
        ]);
    }

    public function choise(Request $request)
    {
        $choices = [
            [
                'type' => PromptType::RecordingScoring,
                'label' => PromptType::RecordingScoring->label(),
                'image' => asset('img/recording-scoring-sample-result.png'),
                'description' => 'This template will generate a score for each recording that has been uploaded. You need to enter relevant prompt to generate maximum result.'
            ]
        ];

        return Inertia::render('recording-analysis/prompt/add/Choise', [
            'choices' => $choices
        ]);
    }

    public function create(Request $request, $type)
    {
        $admin = admin();
        $companies = $admin ? (new UserRepository)->findAllCompany() : [];
        return Inertia::render('recording-analysis/prompt/add/Index', [
            'type' => $type,
            'companies' => $companies,
            'is_admin' => (bool) $admin
        ]);
    }

    public function edit(Request $request, $type, $uuid)
    {
        $admin = admin();
        $item = (new AnalisisPromptRepository)->findFirstByUuid($uuid);
        if ($item->by_admin && !$admin) {
            return to_route('setup.recording-analysis.prompt.index');
        }
        if($item->user_id!=user()?->id && !$admin){
            return to_route('setup.recording-analysis.prompt.index');
        }
        $companies = $admin ? (new UserRepository)->findAllCompany() : [];
        return Inertia::render('recording-analysis/prompt/add/Index', [
            'type' => $type,
            'row' => $item,
            'companies' => $companies,
            'is_admin' => (bool) $admin
        ]);
    }
    public function store(PromptAnalysisRequest $request, $type)
    {
        $id = $request->uuid;
        $admin = admin();
        $message = $id ? 'edited' : 'created';

        $userId = $admin ? $request->company_user_id : user()->id;
        (new AnalisisPromptRepository)->store($request, $type, $userId, (bool) $admin);
        return to_route('setup.recording-analysis.prompt.index')->with(['success' => "Prompt Successfully {$message} !"]);
    }


    public function destroy(Request $request, $uuid)
    {
        $admin = admin();
        $item = (new AnalisisPromptRepository)->findFirstByUuid($uuid);
        if ($item->by_admin && !$admin) {
            return to_route('setup.recording-analysis.prompt.index');
        }

        if($item->user_id!=user()?->id && !$admin){
            return to_route('setup.recording-analysis.prompt.index');
        }
        $item->delete();
        return response()->json('success');
    }

    public function datatable(Request $request)
    {
        $items = (new AnalisisPromptRepository)->datatable(
            $request,
            user()?->id,
            [PromptType::RecordingScoring],
            $request->get('limit', 10),
            (bool) admin()
        );
        return PromptAnalysisResource::collection($items);
    }

}
