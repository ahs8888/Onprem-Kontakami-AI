<?php

namespace App\Http\Controllers\AgentAnalysis;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Enum\AgentProfilingType;
use App\Http\Controllers\Controller;

class AgentProfilingController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('agent-analysis/profiling/Index');
    }

    public function choise(Request $request)
    {
        $choices = [
            // [
            //     'type' => AgentProfilingType::ScoringRecording,
            //     'label' => AgentProfilingType::ScoringRecording->label(),
            //     'image' => asset('img/agent-scoring-on-recording.svg'),
            //     'description' => 'This template will generate a score for each agent based on his/her respective recordings. You need to enter relevant prompt to generate maximum result.'
            // ],[
            //     'type' => AgentProfilingType::ProfilingTemplate,
            //     'label' => AgentProfilingType::ProfilingTemplate->label(),
            //     'image' => asset('img/agent-profiling-template.svg'),
            //     'description' => '[to be describe]'
            // ]
        ];
        return Inertia::render('agent-analysis/profiling/add/Choise', [
            'choices' => $choices
        ]);
    }

    public function create(Request $request,$type){
        return Inertia::render('agent-analysis/profiling/add/Index');
    }
}
