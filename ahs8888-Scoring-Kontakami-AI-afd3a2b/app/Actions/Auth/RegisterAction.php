<?php
namespace App\Actions\Auth;

use App\Helpers\Kontakami;
use App\Helpers\Yellow;
use App\Http\Requests\Auth\RegisterRequest;
use App\Mail\Util\VerificationOtp;
use App\Models\Account\User;
use App\Models\Util\OtpToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class RegisterAction
{
     public function execute(RegisterRequest $request)
     {
          $token = '';
          DB::transaction(function () use ($request, &$token) {
               $email = $request->email;

               $registered = User::query()
                    ->where('email', $request->email)
                    ->first();
               if ($registered) {
                    throw ValidationException::withMessages(['email' => 'Your email is already registered']);
               }

               $phoneCode = $request->phone_code;
               User::updateOrCreate([
                    'email' => $request->email,
               ], [
                    'name' => $request->name,
                    'email' => $request->email,
                    'profile' => config('services.default_avatar'),
                    'password' => Hash::make($request->password),
                    'phone' => Kontakami::phoneNumber($request->phone_number, $phoneCode),
                    'phone_code' => $phoneCode,
                    'company' => $request->company_name,
                    'code' => Kontakami::generateCode()
               ]);


               $token = $this->sendOtp($email,$request->name);
          });

          return $token;
     }

     public function sendOtp($email,$name = null)
     {
          $role = 'users';
          $otp = rand(1111, 9999);
          $otpToken = OtpToken::create([
               'email' => $email,
               'token' => Hash::make($otp),
               'source' => 'register-user',
               'available_until' => now()->addMinutes(2),
               'parent' => $role,
          ]);
          Mail::to($email)->send(new VerificationOtp($otp, 'account',$name));
          logger($otp);
          return OtpToken::createToken($email, $otpToken->token, $role, "register-user");
     }
}