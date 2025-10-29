<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Data\Setting;
use Illuminate\Http\Request;
use App\Actions\Data\SettingAction;
use App\Traits\BadRequestException;
use App\Http\Controllers\Controller;
use App\Traits\RedirectRequestException;

class SettingController extends Controller
{
    public function clouds()
    {
        $token = Setting::where("key", "token")->first();
        return Inertia::render('Setting/Clouds', [
            'token' => $token?->value
        ]);
    }

    public function postClouds(Request $request, SettingAction $settingAction)
    {
        try {
            $settingAction->exceuteClouds($request);

            return to_route('setting.clouds-location')->with([
                'success' => "Successfully update token"
            ]);
        } catch (\Throwable $e) {
            return back()->with([
                'error' => 'Authorization token not found.'
            ]);
        }
    }

    public function accessToken()
    {
        $access_token = Setting::where("key", "access_token")->first();
        return Inertia::render('Setting/AccessToken', [
            'access_token' => $access_token?->value
        ]);
    }

    public function postAccessToken(Request $request, SettingAction $settingAction)
    {
        try {
            $settingAction->exceuteAccessToken($request);

            return to_route('setting.access-token')->with([
                'success' => "Successfully update token"
            ]);
        } catch (BadRequestException $e) {
            return back()->with(['error' => $e->getMessage()]);
        } catch (RedirectRequestException $e) {
            return redirect($e->getMessage());
        }
    }

    public function personal()
    {
        return Inertia::render('Setting/Personal');
    }

    public function postPersonal(Request $request, SettingAction $settingAction)
    {
        try {
            $settingAction->exceutePersonal($request);

            return to_route('setting.personal-setting')->with([
                'success' => "Successfully update account information"
            ]);
        } catch (BadRequestException $e) {
            return back()->with(['error' => $e->getMessage()]);
        } catch (RedirectRequestException $e) {
            return redirect($e->getMessage());
        }
    }

}
