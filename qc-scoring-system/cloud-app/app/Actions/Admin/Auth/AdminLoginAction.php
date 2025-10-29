<?php
namespace App\Actions\Admin\Auth;

use App\Actions\Auth\RegisterAction;
use App\Helpers\Kontakami;
use App\Models\Account\Admin;
use App\Models\Account\User;
use App\Traits\RedirectRequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminLoginAction
{
     public function execute(Request $request)
     {

          $request->validate([
               'email' => 'required|email',
               'password' => 'required|min:8|max:50|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
          ]);

          $admin = Admin::query()
               ->whereEmail($request->email)
               ->first();
          if (!$admin) {
               throw ValidationException::withMessages([
                    'email' => 'The email you entered is incorrect',
               ]);
          }



          if (!Hash::check($request->password, $admin->password)) {
               throw ValidationException::withMessages([
                    'password' => 'The password you entered does not match',
               ]);
          }

          Kontakami::putSessionAdmin($admin);
          return $admin;
     }

}