<?php

namespace App\Models\Analisis;

use App\Enum\PromptType;
use App\Traits\HasDatatable;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class AnalisisPrompt extends Model
{
    use HasUuid,HasDatatable;
    protected $casts = [
        'scorings' => 'array',
        'non_scorings' => 'array',
        'summary' => 'array',
        'summaries' => 'array',
        'type' => PromptType::class
    ];
}
