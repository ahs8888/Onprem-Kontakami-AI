<?php

namespace App\Models\Data;

use App\Models\Data\Recording;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordingDetail extends Model
{
    use HasUuids;

    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'error_log' => 'array',
        ];
    }

    public function recording(): BelongsTo
    {
        return $this->belongsTo(Recording::class);
    }
}
