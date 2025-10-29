<?php

namespace App\Models\Util;

use Illuminate\Database\Eloquent\Model;

class RequestAiLog extends Model
{
    protected $casts = [
        'output' => 'array',
        'prompt_v2' => 'array'
    ];
}
