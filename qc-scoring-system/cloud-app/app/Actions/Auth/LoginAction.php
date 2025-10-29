<?php
namespace App\Actions\Auth;

use App\Actions\Auth\RegisterAction;
use App\Helpers\Kontakami;
use App\Models\Account\User;
use App\Traits\RedirectRequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginAction
{
     public function execute(Request $request)
     {

          $request->validate([
               'email' => 'required|email',
               'password' => 'required|min:8|max:50|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
          ]);

          $user = User::query()
               ->whereEmail($request->email)
               ->first();
          if (!$user) {
               throw ValidationException::withMessages([
                    'email' => 'The email you entered is incorrect',
               ]);
          }

          if (!$user->email_verified_at) {
               /**
                * Send email confirmation OTP again and redirect to input otp form
                */
               $this->sendNewEmailVerification($user->email,$user->name);
          }


          if (!Hash::check($request->password, $user->password)) {
               throw ValidationException::withMessages([
                    'password' => 'The password you entered does not match',
               ]);
          }

          Kontakami::putSessionUser($user);
          return $user;
     }

     private function forceLogoutUser(User $user)
     {
         
     }


     private function sendNewEmailVerification($email,$name)
     {
          $token = (new RegisterAction)->sendOtp($email,$name);
          throw new RedirectRequestException(route('auth.register.otp-verification', $token));
     }
}