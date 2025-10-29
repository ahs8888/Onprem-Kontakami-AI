<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordingDetailResource extends JsonResource
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
            'name' => $this->name,
            'file' => $this->file,
            'is_transcript' => $this->is_transcript,
            // 'status' => $this->recording->status == 'Progress' || $this->recording->status == 'Queue' ? '' : (!$this->error_log ? 'Success' : 'Failed')
            'status' => $this->status
        ];
    }
}
