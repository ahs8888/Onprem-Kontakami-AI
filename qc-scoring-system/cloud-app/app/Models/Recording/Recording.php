<?php

namespace App\Models\Recording;

use App\Traits\HasUuid;
use App\Traits\HasDatatable;
use App\Models\Recording\RecordingFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recording extends Model
{
    use HasUuid, SoftDeletes, HasDatatable;

    public function files(): HasMany
    {
        return $this->hasMany(RecordingFile::class, 'recording_id');
    }
}
