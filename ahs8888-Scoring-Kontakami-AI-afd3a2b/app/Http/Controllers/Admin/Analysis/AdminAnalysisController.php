<?php

namespace App\Http\Controllers\Admin\Analysis;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repository\Analisis\AdminAnalysisRecoringRepository;
use App\Actions\Admin\RecordingList\ExportRecordingListAction;

class AdminAnalysisController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('admin/analysis/Index');
    }

    public function datatable(Request $request)
    {
        return (new AdminAnalysisRecoringRepository)->datatable($request, $request->get('limit', 10));
    }

    public function export(Request $request, ExportRecordingListAction $exportRecordingListAction)
    {
        return $exportRecordingListAction->execute($request);
    }
}
