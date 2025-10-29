<?php

namespace App\Models\Account;

use App\Models\Analisis\AnalisisPrompt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserSetting extends Model
{
    //
    public function prompt() : HasOne{
        return $this->hasOne(AnalisisPrompt::class,'id','analisis_prompt_id');
    }
}
