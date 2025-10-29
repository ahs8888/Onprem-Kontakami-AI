<?php

namespace App\Models\Analisis;

use App\Traits\HasDatatable;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class AgentScoringItem extends Model
{
    use HasUuid,HasDatatable;

    protected $appends = [
        'uuid'
    ];
    protected $casts = [
        'scoring' => 'array',
        'summary_temp' => 'array',
        'summary' => 'array',
        'analysis_scorings_id' => 'array',
        'recording_files_id' => 'array',
        'files' => 'array',
    ];

    public function getCreatedAtAttribute($value)
    {
        return date('d-M-Y H:i',strtotime($value));
    }
}
