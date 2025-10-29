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
            'requires_ticket' => 'boolean',
            'linked_at' => 'datetime',
            'pii_detected' => 'boolean',
            'pii_types' => 'array',
            'is_encrypted' => 'boolean',
            'cleaning_metadata' => 'array',
            'processed_at' => 'datetime',
            'last_retry_at' => 'datetime',
        ];
    }
    
    /**
     * Scope to get unlinked recordings that require tickets
     */
    public function scopeUnlinked($query)
    {
        return $query->where('requires_ticket', true)
                     ->whereNull('ticket_id');
    }
    
    /**
     * Scope to get linked recordings
     */
    public function scopeLinked($query)
    {
        return $query->where('requires_ticket', true)
                     ->whereNotNull('ticket_id');
    }
    
    /**
     * Accessor for display name with fallback
     */
    public function getDisplayNameAttribute($value)
    {
        return $value ?? $this->name;
    }

    public function recording(): BelongsTo
    {
        return $this->belongsTo(Recording::class);
    }
}
