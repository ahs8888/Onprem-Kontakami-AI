<?php
namespace App\Actions\Auth;

use App\Models\Account\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordToken
{
     public function execute(Request $request, $token)
     {
          $password = Hash::make($request->password);
          User::where('email', $token->email)
               ->update([
                    'password' => $password
               ]);
     }
}