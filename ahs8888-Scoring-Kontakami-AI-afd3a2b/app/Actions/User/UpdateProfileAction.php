<?php
namespace App\Actions\User;

use App\Helpers\Kontakami;
use App\Models\Account\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UpdateProfileAction
{
     public function handle(Request $request, $userId)
     {
          $user = User::where('id', $userId)->firstOrFail();

          $phoneCode = $request->phone_code;
          $userProps = [
               'name' => $request->name,
               'phone' => Kontakami::phoneNumber($request->phone_number, $phoneCode),
               'phone_code' => $phoneCode,
          ];
          if($request->company){
               $userProps['company'] = $request->company;
          }

          // validate if change password
          if ($request->password && $request->confirm_password && $request->new_password) {
               if ($request->confirm_password != $request->new_password) {
                    throw ValidationException::withMessages(['new_password' => 'Password confirmation is invalid !']);
               }

               if (!Hash::check($request->password, $user->password)) {
                    throw ValidationException::withMessages([
                         'password' => 'The current password you entered does not match',
                    ]);
               }

                   if ($request->password == $request->new_password) {
                    throw ValidationException::withMessages(['new_password' => 'New password cannot be the same as your current password']);
               }
               $userProps['password'] = Hash::make($request->new_password);
          }

          if ($request->hasFile('avatar')) {
               $userProps['profile'] = 'storage/' . $request->file('avatar')->store('profile', 'public');
          }
          $user->update($userProps);

          
          Kontakami::putSessionUser($user);
     }
}