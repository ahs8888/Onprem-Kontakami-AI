<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginAction;
use App\Http\Controllers\Controller;
use App\Traits\BadRequestException;
use App\Traits\RedirectRequestException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthLoginController extends Controller
{
    public function index(Request $request)
    {
        if (user()) {
            return to_route('setup.recording-analysis.prompt.index');
        }

        return Inertia::render("auth/Login");
    }

    public function store(Request $request, LoginAction $loginAction)
    {
        try {
            $loginAction->execute($request);

            return to_route('setup.recording-analysis.prompt.index');
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
            $message = 'Your session has expired';
        }
        session()->forget(config('services.session-user-prefix'));
        return to_route('auth.login.index')->with(['error' => $message]);
    }
}
