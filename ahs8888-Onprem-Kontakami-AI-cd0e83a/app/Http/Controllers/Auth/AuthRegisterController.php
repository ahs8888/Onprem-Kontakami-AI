<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterAction;
use App\Http\Controllers\Controller;
use App\Traits\BadRequestException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthRegisterController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render("Auth/Register");
    }

    public function store(Request $request, RegisterAction $registerAction)
    {
        $token = $registerAction->execute($request);
        return to_route('auth.login.index')->with([
            'success' => "Success create account"
        ]);
    }
}
