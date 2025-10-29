<?php

namespace App\Http\Controllers\Api;

use App\Traits\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\BadRequestException;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Api\CreateRecordingRequest;
use App\Repository\Recording\RecordingRepository;
use App\Http\Requests\Api\InjectRecordingFileRequest;
use App\Actions\RecordingFile\CreateRecordingDataAction;
use App\Actions\RecordingFile\DeleteRecordingDataAction;
use App\Actions\RecordingFile\InjectRecordingFileAction;

class ApiRecordingController extends Controller
{
    use JsonResponse;
    public function store(CreateRecordingRequest $request, CreateRecordingDataAction $createRecordingDataAction)
    {
        try {
            $recording = $createRecordingDataAction->handle($request, $request->get('SESSION_USER_ID'));
            return $this->success([
                'uuid' => $recording?->uuid
            ], 'Successfuly!');
        } catch (\Exception $e) {
            return $this->badRequest($e->getMessage());
        }
    }

    public function inject(InjectRecordingFileRequest $request, InjectRecordingFileAction $injectRecordingFileAction, $uuid)
    {
        try {
            $injectRecordingFileAction->handle($request,id_from_uuid($uuid),$request->get('SESSION_USER_ID'));
            return $this->message('Successfuly!');
        } catch (BadRequestException $e) {
            return $this->badRequest($e->getMessage());
        } catch (\Exception $e) {
            return $this->badRequest($e->getMessage());
        }
    }

    public function destroy(Request $request,DeleteRecordingDataAction $deleteRecordingDataAction, $uuid)
    {
        try {
            $deleteRecordingDataAction->handle($uuid, $request->get('SESSION_USER_ID'));
            return $this->message('Successfuly!');
        } catch (\Exception $e) {
            return $this->badRequest($e->getMessage());
        }
    }
}
