<?php

namespace App\Models\Recording;

use App\Helpers\Kontakami;
use App\Traits\HasDatatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class RecordingFile extends Model
{
    use HasDatatable;
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
