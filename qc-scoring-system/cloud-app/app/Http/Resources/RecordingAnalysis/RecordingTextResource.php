<?php

namespace App\Http\Resources\RecordingAnalysis;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordingTextResource extends JsonResource
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
            'folder' => $this->folder,
            'total_file' => $this->total_file,
            // 'total_token' => $this->total_token,
            'is_new' => (boolean) $this->is_new,
            'analisis_prompt_id' => $this->analisis_prompt_id,
            'in_use' => (boolean) $this->in_use
        ];
    }
}
