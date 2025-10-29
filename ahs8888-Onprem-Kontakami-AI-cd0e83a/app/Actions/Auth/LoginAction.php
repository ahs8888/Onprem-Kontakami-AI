<?php
namespace App\Actions\Auth;

use App\Helpers\Yellow;
use App\Models\Account\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\RedirectRequestException;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    public function execute(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required|min:8|max:50|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
        ]);

        $user = User::query()
            ->whereUsername($request->username)
            ->whereNull('deleted_at')
            ->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'username' => 'The username you entered is incorrect',
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'The password you entered does not match',
            ]);
        }

        $sessionObject = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'avatar' => asset($user->profile),
        ];

        session()->put(config('services.session-user-prefix'), (object) $sessionObject);
        return $user;
    }
}
