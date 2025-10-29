<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginAction;
use App\Http\Controllers\Controller;
use App\Models\Util\Setting;
use App\Traits\BadRequestException;
use App\Traits\RedirectRequestException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthLoginController extends Controller
{
    public function index(Request $request)
    {
        if (user()) {
            return to_route('recordings.index');
        }

        return Inertia::render("Auth/Login");
    }

    public function store(Request $request, LoginAction $loginAction)
    {
        try {
            $loginAction->execute($request);

            return to_route('recordings.index');
        } catch (BadRequestException $e) {
            return back()->with(['error' => $e->getMessage()]);
        } catch (RedirectRequestException $e) {
            return redirect($e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        $message = null;
        if ($request->force) {
            $message = 'You already login in another device';
        }
        session()->flush();
        return to_route('auth.login.index')->with(['error' => $message]);
    }
}
