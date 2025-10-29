<?php
namespace App\Actions\Auth;

use App\Helpers\Yellow;
use App\Models\Account\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegisterAction
{
    public function execute(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required|min:8|max:50|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
        ]);

        DB::transaction(function () use ($request) {
            $username = $request->username;

            $registered = User::query()
                ->where('username', $request->username)
                ->whereNull('deleted_at')
                ->first();
            if ($registered) {
                throw ValidationException::withMessages(['username' => 'Your username is already registered as Customer']);
            }

            User::updateOrCreate([
                'username' => $request->username,
            ], [
                'name' => $request->username,
                'username' => $request->username,
                'profile' => config('services.default_avatar'),
                'password' => Hash::make($request->password)
            ]);
        });
    }
}
