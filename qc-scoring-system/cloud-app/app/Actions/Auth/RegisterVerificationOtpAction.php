<?php
namespace App\Actions\Auth;

use App\Models\Account\User;
use App\Models\Util\OtpToken;
use App\Traits\BadRequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterVerificationOtpAction
{
     public function execute(Request $request, $token)
     {
          $request->validate(['otp' => 'required|array']);
          $otp = implode('', $request->otp);
          $email = OtpToken::findDataFromToken($token, 0);
          $action = OtpToken::findDataFromToken($token, 3);
          $validSourceAction = "register-user";
          if ($action != $validSourceAction) {
               throw new BadRequestException('Your OTP is incorrect. Please try again');
          }

          if (!$otpToken = OtpToken::where('source', $validSourceAction)->activeToken($email)->first()) {
               throw new BadRequestException('Your OTP is expired, please crete a new request again.');
          }

          if (!Hash::check($otp, $otpToken->token)) {
               throw new BadRequestException('Your OTP is incorrect. Please try again');
          }

          User::query()
               ->whereEmail($otpToken->email)
               ->update([
                    'email_verified_at' => now()
               ]);
     }
}