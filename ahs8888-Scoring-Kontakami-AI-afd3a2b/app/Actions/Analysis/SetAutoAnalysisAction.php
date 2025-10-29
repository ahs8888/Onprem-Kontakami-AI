<?php
namespace App\Actions\Analysis;

use App\Models\Account\UserSetting;
use App\Repository\Util\UserSettingRepository;
use Illuminate\Http\Request;

class SetAutoAnalysisAction
{
     public function handle(Request $request, $userId)
     {
          return (new UserSettingRepository)->setAutoAnalysis($request->prompt_id,$userId);
     }
}