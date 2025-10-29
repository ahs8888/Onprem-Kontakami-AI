<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Data\Recording;
use App\Services\RecordingService;
use App\Services\TicketLinkingService;
use App\Traits\BadRequestException;
use App\Http\Controllers\Controller;
use App\Actions\Data\RecordingAction;
use App\Traits\RedirectRequestException;
use App\Http\Resources\RecordingResource;
use App\Http\Resources\RecordingDetailResource;
use App\Models\Data\RecordingDetail;

class RecordingController extends Controller
{
    public function redirect()
    {
        return to_route('recordings.index');
    }

    public function index()
    {
        $ticketService = app(TicketLinkingService::class);
        
        return Inertia::render('Recording/Index', [
            'unlinkedCount' => $ticketService->getUnlinkedCount()
        ]);
    }

    public function datatable(Request $request)
    {
        return RecordingResource::collection(
            (new RecordingService())->getList(
                request: $request,
                limit: $request->get('limit', 10)
            )
        );
    }

    public function detail($id)
    {
        (new RecordingAction())->read($id);
        return Inertia::render('Recording/Detail', [
            'data' => Recording::where("id", $id)->firstOrFail()
        ]);
    }

    public function retry($id)
    {
        (new RecordingAction())->retry($id);
        return response()->json("oke");
    }

    public function transcript($id, $detailId)
    {
        return Inertia::render('Recording/Transcript', [
            'data' => Recording::where("id", $id)->firstOrFail(),
            'detail' => RecordingDetail::where("id", $detailId)->firstOrFail(),
        ]);
    }

    public function detailDatatable(Request $request, $id)
    {
        return RecordingDetailResource::collection(
            (new RecordingService())->getListDetail(
                id: $id,
                request: $request,
                limit: $request->get('limit', 10)
            )
        );
    }

    public function storeFolder(Request $request, RecordingAction $recordingAction)
    {
        try {
            $recordingAction->storeFolder($request);

            return response()->json([
                "message" => "Successfully create new recording"
            ], 200);
        } catch (BadRequestException $e) {
            logger("ERROR STORE FOLDER", [
                'data' => $e
            ]);
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function deleteFolder(Request $request, RecordingAction $recordingAction)
    {
        try {
            $recordingAction->deleteFolder($request);

            return back()->with([
                'success' => "Successfully delete recording"
            ]);
        } catch (BadRequestException $e) {
            return back()->with(['error' => $e->getMessage()]);
        } catch (RedirectRequestException $e) {
            return redirect($e->getMessage());
        }
    }
}
