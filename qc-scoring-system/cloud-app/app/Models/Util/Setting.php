<?php

namespace App\Models\Util;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'settings';

    protected $guarded = [];

    public function scopeFindValue($query, $key)
    {
        return $query->where('key',$key)->value('value');
    }

    public function scopeKeys($query, $keys = [])
    {
        return $query->whereIn('key', $keys)->pluck('value','key');
    }
}
