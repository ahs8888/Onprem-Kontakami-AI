<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ForgotPasswordOtpAction;
use App\Actions\Auth\UpdatePasswordToken;
use App\Http\Controllers\Controller;
use App\Models\Util\OtpToken;
use App\Traits\BadRequestException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthForgotPasswordController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render("auth/ForgotPassword");
    }


    public function verification(Request $request, $token)
    {
        $email = OtpToken::findDataFromToken($token, 0);
        abort_if(!$email, 404);
        return Inertia::render('auth/VerificationOtp', [
            'email' => $email,
            'action' => route("auth.forgot-password.verification", $token),
            'resend_url' => route('auth.forgot-password.otp-send', $token),
            'token' => $token,
        ]);
    }

    public function changePassword(Request $request, $tokenId)
    {
        OtpToken::findOrFail(id_from_uuid($tokenId));
        return Inertia::render('auth/ResetPassword', [
            'tokenId' => $tokenId
        ]);
    }

    public function sendOtp(Request $request, ForgotPasswordOtpAction $forgotPasswordOtpAction)
    {
        $request->validate(['email' => 'required|email']);
        $token = $forgotPasswordOtpAction->execute($request->email);
        return to_route('auth.forgot-password.otp-verification', $token);
    }

    public function postVerification(Request $request, ForgotPasswordOtpAction $forgotPasswordOtpAction, $token)
    {
        try {
            $tokenId = $forgotPasswordOtpAction->validateOtp($request, $token);
            return to_route('auth.forgot-password.change-password', id_to_uuid($tokenId));
        } catch (BadRequestException $e) {
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function updatePassword(Request $request, UpdatePasswordToken $updatePasswordToken, $tokenId)
    {
        try {
            $request->validate([
                'password' => 'required|min:8|max:50|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
                'confirm_password' => 'required|min:8|max:50|same:password|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
            ]);

            $token = OtpToken::where('id', id_from_uuid($tokenId))->first();

            if (!$token) {
                return to_route('auth.forgot-password.index')->with(['error' => 'Your token is not found or expired, please crete a new request again.']);
            }


            $updatePasswordToken->execute($request, $token);

            return to_route('auth.login.index')->with(['success' => 'Your password was changed successfully.']);
        } catch (BadRequestException $e) {
            return back()->with(['error' => $e->getMessage()]);
        }
    }
}
