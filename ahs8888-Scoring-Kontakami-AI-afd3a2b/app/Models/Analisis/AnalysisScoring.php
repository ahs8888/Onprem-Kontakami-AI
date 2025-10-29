<?php

namespace App\Models\Analisis;

use App\Traits\HasUuid;
use App\Helpers\Kontakami;
use App\Traits\HasDatatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class AnalysisScoring extends Model
{
    use HasUuid, HasDatatable;

    protected $table = 'analysis_scorings';

    protected $appends = [
        'uuid'
    ];
    protected $casts = [
        'scoring' => 'array',
        'non_scoring' => 'array',
        'summary' => 'array',
    ];

    protected function conversations(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attribute) {
                $transcribe = preg_split("/\r\n|\r|\n/", $attribute['transcribe']);
                return Kontakami::transcribeToConversation($transcribe);
            }
        );
    }

    
    public function getCreatedAtAttribute($value)
    {
        return date('d-M-Y H:i',strtotime($value));
    }
}
