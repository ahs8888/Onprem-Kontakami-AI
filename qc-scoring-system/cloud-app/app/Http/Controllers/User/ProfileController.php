<?php

namespace App\Http\Controllers\User;

use App\Traits\BadRequestException;
use Inertia\Inertia;
use App\Helpers\Kontakami;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Actions\User\UpdateProfileAction;

class ProfileController extends Controller
{
    public function personal(Request $request)
    {
        return Inertia::render('setting/Personal', [
            'phone_country' => Kontakami::phoneCountryCode()
        ]);
    }

    public function updateProfile(Request $request, UpdateProfileAction $updateProfileAction)
    {
        $request->validate([
            'name' => 'required',
            'phone_number' => 'required',
            'phone_code' => 'required',
            'company_required',
        ]);
        if ($request->password && $request->confirm_password && $request->new_password) {
            $request->validate([
                'password' => 'required|min:8|max:50|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
                'new_password' => 'required|min:8|max:50|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
                'confirm_password' => 'required|min:8|max:50|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
            ]);
        }

        try {
            $updateProfileAction->handle($request, user()->id);
            return back()->with(['success' => 'Personal Setting successfuly updated !']);
        } catch (BadRequestException $e) {
            return back()->with(['error' => $e->getMessage()]);
        }
    }
}
