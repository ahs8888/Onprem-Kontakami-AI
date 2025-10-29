<?php
namespace App\Repository\Util;

use App\Models\Account\UserSetting;

class UserSettingRepository
{
     public function __construct(
          public $model = UserSetting::class
     ) {
     }
     public function setAutoAnalysis($promptId, $userId)
     {
          return $this->model::updateOrCreate([
               'user_id' => $userId,
          ], [
               'analisis_prompt_id' => $promptId
          ]);
     }

     public function find($userId)
     {
          return $this->model::where('user_id', $userId)->first();
     }

}