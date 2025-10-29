<?php

namespace App\Http\Controllers\Admin\Auth;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Traits\BadRequestException;
use App\Http\Controllers\Controller;
use App\Traits\RedirectRequestException;
use App\Actions\Admin\Auth\AdminLoginAction;

class AdminAuthController extends Controller
{
    public function index(Request $request)
    {
        if (admin()) {
            return to_route('admin.analysis-record.index');
        }

        return Inertia::render('admin/auth/Login');
    }

    public function store(Request $request, AdminLoginAction $adminLoginAction)
    {
        try {
            $adminLoginAction->execute($request);

            return to_route('admin.analysis-record.index');
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
        session()->forget(config('services.session-admin-prefix'));
        return to_route('admin.auth.login.index')->with(['error' => $message]);
    }
}
