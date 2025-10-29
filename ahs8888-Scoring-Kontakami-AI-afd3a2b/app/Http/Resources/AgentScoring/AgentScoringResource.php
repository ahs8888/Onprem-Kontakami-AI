<?php

namespace App\Http\Resources\AgentScoring;

use App\Enum\ProcessStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentScoringResource extends JsonResource
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
            'foldername' => $this->foldername,
            'analysis_name' => $this->analysis_name,
            'total_data' => $this->total_data,
            'status' => $this->status,
            'is_new' => $this->status==ProcessStatus::Done && $this->is_new ? true : false,
            'is_failed' => $this->status==ProcessStatus::Failed
        ];
    }
}
