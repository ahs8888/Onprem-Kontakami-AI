<?php

namespace App\Models\Analisis;

use App\Enum\ProcessStatus;
use App\Traits\HasUuid;
use App\Traits\HasDatatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Analysis extends Model
{
    use HasUuid, HasDatatable, SoftDeletes;

    protected $table = 'analysis';
    protected $casts = [
        'prompt' => 'array',
        'status' => ProcessStatus::class
    ];

    public function scorings(): HasMany
    {
        return $this->hasMany(AnalysisScoring::class, 'analysis_id');
    }
}
