<?php

namespace App\Models\Data;

use App\Models\Data\Recording;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

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
            'is_transcript' => 'boolean',
            'transfer_cloud' => 'boolean',
            'requires_ticket' => 'boolean',
            'linked_at' => 'datetime',
        ];
    }

    /**
     * Get the recording that owns the detail.
     */
    public function recording(): BelongsTo
    {
        return $this->belongsTo(Recording::class);
    }

    /**
     * Get display name with fallback.
     */
    public function getDisplayNameAttribute($value): string
    {
        if ($value) {
            return $value;
        }
        
        // Fallback: filename + timestamp
        return $this->name . ' - ' . $this->created_at->format('Y-m-d H:i');
    }

    /**
     * Scope to get unlinked recordings (requiring tickets but not linked yet).
     */
    public function scopeUnlinked(Builder $query): Builder
    {
        return $query->where('requires_ticket', true)
                     ->whereNull('ticket_id');
    }

    /**
     * Scope to get linked recordings (have ticket information).
     */
    public function scopeLinked(Builder $query): Builder
    {
        return $query->whereNotNull('ticket_id');
    }

    /**
     * Scope to get recordings that require ticket linking.
     */
    public function scopeRequiresTicket(Builder $query): Builder
    {
        return $query->where('requires_ticket', true);
    }

    /**
     * Scope to get recordings that don't need ticket linking.
     */
    public function scopeNoTicketNeeded(Builder $query): Builder
    {
        return $query->where('requires_ticket', false);
    }

    /**
     * Scope to search recordings by display name, customer, or ticket.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('display_name', 'like', "%{$search}%")
              ->orWhere('customer_name', 'like', "%{$search}%")
              ->orWhere('ticket_id', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%");
        });
    }

    /**
     * Check if recording is linked to a ticket.
     */
    public function isLinked(): bool
    {
        return !is_null($this->ticket_id);
    }

    /**
     * Check if recording requires ticket but is not linked.
     */
    public function isPendingTicket(): bool
    {
        return $this->requires_ticket && is_null($this->ticket_id);
    }
}
