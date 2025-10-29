<?php

namespace App\Models\Util;

use App\Enum\ProcessType;
use App\Enum\ProcessStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ProcessLog extends Model
{
    use HasUuid;
    protected $casts = [
        'status' => ProcessStatus::class,
        'type' => ProcessType::class,
        'data' => 'array'
    ];
}
