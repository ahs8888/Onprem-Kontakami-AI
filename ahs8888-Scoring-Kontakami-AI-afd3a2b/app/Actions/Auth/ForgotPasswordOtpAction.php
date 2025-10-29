<?php
namespace App\Actions\Auth;

use App\Mail\Util\VerificationOtp;
use App\Models\Account\User;
use App\Models\Util\OtpToken;
use App\Traits\BadRequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ForgotPasswordOtpAction
{
     public function execute($email)
     {
          $user = User::query()
               ->whereEmail($email)
               ->first();
          if (!$user) {
               throw ValidationException::withMessages([
                    'email' => 'The email you entered is incorrect'
               ]);
          }

          return $this->sendOtp($email,$user->name);
     }

     public function validateOtp(Request $request, $token)
     {
          $request->validate(['otp' => 'required|array']);
          $otp = implode('', $request->otp);
          $email = OtpToken::findDataFromToken($token, 0);
          $action = OtpToken::findDataFromToken($token, 3);
          $validSourceAction = "forgot-password-user";
          if ($action != $validSourceAction) {
               throw new BadRequestException('Your OTP is incorrect. Please try again');
          }

          if (!$otpToken = OtpToken::where('source', $validSourceAction)->activeToken($email)->first()) {
               throw new BadRequestException('Your OTP is expired, please crete a new request again.');
          }

          if (!Hash::check($otp, $otpToken->token)) {
               throw new BadRequestException('Your OTP is incorrect. Please try again');
          }

          return $otpToken->id;
     }

     public function sendOtp($email,$name = null)
     {
          $role = "user";
          $otp = rand(1111, 9999);
          $source = "forgot-password-user";
          $otpToken = OtpToken::create([
               'email' => $email,
               'token' => Hash::make($otp),
               'source' => $source,
               'available_until' => now()->addMinutes(2),
               'parent' => $role,
          ]);
          Mail::to($email)->send(new VerificationOtp($otp, 'forgot-password',$name));
          logger($otp);
          return OtpToken::createToken($email, $otpToken->token, $role, $source);
     }
}