<?php

namespace App\Actions\Data;

use App\Models\Account\User;
use App\Models\Data\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class SettingAction
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function exceuteClouds(Request $request)
    {
        $request->validate([
            'token' => 'required'
        ]);

        $response = Http::withToken($request->token)
            ->post(config("services.clouds_url")."/api/external/v1/ping");


        if ($response->failed()) {
            throw new \Exception('Authorization token not found.');
        }

        Setting::updateOrCreate(
            [
                'key' => 'token'
            ],
            [
                'value' => $request->token
            ]
        );

    }

    public function exceuteAccessToken(Request $request)
    {
        $request->validate([
            'access_token' => 'required'
        ]);

        Setting::updateOrCreate(
            [
                'key' => 'access_token'
            ],
            [
                'value' => $request->access_token
            ]
        );

    }

    public function exceutePersonal(Request $request)
    {
        $request->validate([
            'fullname' => 'required',
            'current_password' => 'nullable|min:8|max:50|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
            'new_password' => 'required_with:current_password|nullable|min:8|max:50|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
            'confirm_new_password' => 'required_with:new_password|nullable|same:new_password|min:8|max:50|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
        ]);

        $user = User::query()
            ->where("id", user()->id)
            ->whereNull('deleted_at')
            ->first();

        $data = [];

        if ($request->current_password) {
            if (!Hash::check($request->current_password, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'The password you entered does not match',
                ]);
            }

            if (Hash::check($request->new_password, $user->password)) {
                throw ValidationException::withMessages([
                    'new_password' => 'The new password must be different from your current password',
                ]);
            }

            $data['password'] = Hash::make($request->new_password);
        }

        $data['name'] = $request->fullname;

        $user->update($data);

        $session = user();

        $sessionObject = [
            'id' => $session->id,
            'name' => $request->fullname,
            'username' => $session->username,
            'avatar' => $session->avatar,
        ];

        session()->put(config('services.session-user-prefix'), (object) $sessionObject);


    }
}
