<?php

namespace Privateer\Moments\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Privateer\Moments\Http\Requests\UpdatePasswordRequest;
use Privateer\Moments\Http\Requests\UpdateProfileRequest;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $tokens = $user->tokens()->latest()->get();

        return view('moments::account.show', ['user' => $user, 'tokens' => $tokens]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('account.show')->with('profile_updated', true);
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->validated()['password']]);

        return redirect()->route('account.show')->with('password_updated', true);
    }
}
