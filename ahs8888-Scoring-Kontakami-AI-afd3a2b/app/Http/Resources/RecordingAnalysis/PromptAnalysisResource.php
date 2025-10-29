<?php

namespace App\Http\Resources\RecordingAnalysis;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromptAnalysisResource extends JsonResource
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
            'in_use' => $this->in_use ? true : false,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'type' => $this->type,
            'type_label' => $this->type?->label(),
            'created_at' => $this->created_at->format('d-M-Y, H:i'),
            'by_admin' => $this->by_admin,
            'company' => $this->company,
        ];
    }
}
