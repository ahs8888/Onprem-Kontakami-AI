<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Data\Recording;
use App\Models\Data\RecordingDetail;

class RecordingService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getList(Request $request, $limit = 10)
    {
        $search = $request->search;
        $filter = $request->get('filter', []);
        $created_start = @$filter['created_start'];
        $created_end = @$filter['created_end'];
        $status = @$filter['status'];

        if ($status && is_array($status) && in_array('All', $status)) {
            $status = [
                "Queue",
                "Progress",
                "Done"
            ];
        }

        return Recording::query()
            ->with('details')
            ->when($search, fn ($q) => $q->whereLike("folder_name", $search))
            ->when($created_start && $created_end, fn($query) => $query->whereBetween('created_at', [$created_start . " 00:00:00", $created_end . " 23:59:59"]))
            ->when($status, fn ($q) => $q->whereIn("status", $status))
            ->orderByRaw("COALESCE(injected_at, created_at) DESC")
            ->paginate($limit);
    }

    public function getListDetail($id, Request $request, $limit = 10)
    {
        $filter = $request->get('filter', []);
        $status = @$filter['status'];

        if ($status && is_array($status) && in_array('All', $status)) {
            $status = [
                "Success",
                "Failed",
                "Progress"
            ];
        }

        return RecordingDetail::with(['recording'])
            ->where("recording_id", $id)
            ->when($status, fn ($q) => $q->whereIn("status", $status))
            ->orderBy("sort", "asc")
            ->paginate($limit);
    }
}
