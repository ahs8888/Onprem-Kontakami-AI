<?php

namespace App\Http\Resources\RecordingAnalysis;

use App\Enum\ProcessStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalysisResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'created_at' => $this->created_at->format('d-M-Y, H:i'),
            'name' => $this->name ?: "Analysis {$this->foldername}",
            'foldername' => $this->foldername,
            'prompt_name' => $this->prompt_name,
            'total_file' => $this->total_file,
            'status' => $this->status,
            'is_new' => $this->status==ProcessStatus::Done && $this->is_new ? true : false,
            'is_failed' => $this->status==ProcessStatus::Failed
        ];
    }
}
