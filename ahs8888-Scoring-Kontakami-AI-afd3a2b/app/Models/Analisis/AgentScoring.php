<?php

namespace App\Models\Analisis;

use App\Traits\HasDatatable;
use App\Traits\HasUuid;
use App\Enum\ProcessStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentScoring extends Model
{
    use HasUuid,HasDatatable,SoftDeletes;

    protected $casts = [
        'status' => ProcessStatus::class
    ];

    public function items(): HasMany
    {
        return $this->hasMany(AgentScoringItem::class, 'agent_scoring_id');
    }
}
