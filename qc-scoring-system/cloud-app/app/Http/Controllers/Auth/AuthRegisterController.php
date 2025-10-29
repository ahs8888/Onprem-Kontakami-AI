<?php

namespace App\Http\Controllers\Auth;

use Inertia\Inertia;
use App\Helpers\Kontakami;
use App\Models\Account\User;
use App\Models\Util\Setting;
use Illuminate\Http\Request;
use App\Models\Util\OtpToken;
use App\Traits\BadRequestException;
use App\Actions\Auth\RegisterAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Actions\Auth\RegisterVerificationOtpAction;

class AuthRegisterController extends Controller
{
    public function index(Request $request)
    {
        $setting = Setting::keys(['term_condition_en', 'privacy_policies_en']);

        return Inertia::render("auth/Register", [
            'term_condition' => @$setting['term_condition_en'],
            'privacy_policy' => @$setting['privacy_policies_en'],
            'phone_country' => Kontakami::phoneCountryCode()
        ]);
    }

    public function verification(Request $request, $token)
    {
        $email = OtpToken::findDataFromToken($token, 0);
        abort_if(!$email, 404);
        return Inertia::render('auth/VerificationOtp', [
            'email' => $email,
            'action' => route("auth.register.verification", $token),
            'resend_url' => route('auth.register.otp-resend', $token),
            'token' => $token,
        ]);
    }

    public function store(RegisterRequest $request, RegisterAction $registerAction)
    {
        $token = $registerAction->execute($request);
        return to_route('auth.register.otp-verification', $token);
    }

    public function postVerification(Request $request, RegisterVerificationOtpAction $registerVerificationOtpAction, $token)
    {
        try {
            $registerVerificationOtpAction->execute($request, $token);
            return to_route('auth.login.index');
        } catch (BadRequestException $e) {
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function resentOtp(Request $request, RegisterAction $registerAction, $token)
    {
        $user = User::where('email', $request->email)->first();
        $newToken = $registerAction->sendOtp($request->email, $user?->name);
        return to_route('auth.register.otp-verification', $newToken);
    }
}
