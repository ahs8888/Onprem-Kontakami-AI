<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ResetAction;
use App\Http\Controllers\Controller;
use App\Traits\BadRequestException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthResetPasswordController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render("Auth/ResetPassword");
    }

    public function store(Request $request, ResetAction $reset)
    {
        $token = $reset->execute($request);
        return to_route('auth.login.index')->with([
            'success' => "Success reset password"
        ]);
    }
}
