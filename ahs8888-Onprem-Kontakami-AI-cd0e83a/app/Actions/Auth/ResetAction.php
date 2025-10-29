<?php
namespace App\Actions\Auth;

use App\Helpers\Yellow;
use App\Models\Account\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ResetAction
{
    public function execute(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required|min:8|max:50|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
            'confirm_password' => 'same:password|min:8|max:50|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
        ]);

        DB::transaction(function () use ($request) {
            $username = $request->username;

            $user = User::query()
                ->where('username', $request->username)
                ->whereNull('deleted_at')
                ->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'username' => 'The username you entered is incorrect',
                ]);
            }

            if (Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'password' => 'The new password must be different from your current password',
                ]);
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);
        });
    }
}
