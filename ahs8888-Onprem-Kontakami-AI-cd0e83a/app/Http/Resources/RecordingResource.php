<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $transcripted = 0;
        $success = 0;
        $failed = 0;

        if ($this->status != 'Progress' && $this->status != 'Queue') {
            foreach ($this->details as $key => $value) {
                if ($value->is_transcript) $transcripted += 1;
                if ($value->status == 'Success') $success += 1;
                if ($value->status == 'Failed') $failed += 1;
            }
        }

        return [
            'id' => $this->id,
            'date' => Carbon::parse($this->injected_at ?? $this->created_at)->format("d M Y, H.i"),
            'name' => $this->folder_name,
            'total_data' => count($this->details),
            'token' => $this->token,
            'progress' => $transcripted ."/".count($this->details),
            'status' => $this->status,
            'is_read' => $this->is_read,
            'total_success' => $success,
            'total_failed' => $failed
        ];
    }
}
