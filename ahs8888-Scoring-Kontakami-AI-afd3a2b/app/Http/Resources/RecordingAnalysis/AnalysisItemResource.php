<?php

namespace App\Http\Resources\RecordingAnalysis;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalysisItemResource extends JsonResource
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
            'created_at' => $this->created_at,
            'filename' => $this->filename,
            'conversations' => $this->conversations,
            'is_done' => $this->is_done,
            'avg_score' => $this->avg_score,
            'uuid' => $this->uuid,
        ];
    }
}
