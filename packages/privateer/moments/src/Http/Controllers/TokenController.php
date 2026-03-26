<?php

namespace Privateer\Moments\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Privateer\Moments\Http\Requests\StoreTokenRequest;

class TokenController extends Controller
{
    public function store(StoreTokenRequest $request): RedirectResponse
    {
        $token = $request->user()->createToken($request->validated()['name']);

        return redirect()->route('account.show')
            ->with('plain_text_token', $token->plainTextToken);
    }

    public function destroy(Request $request, PersonalAccessToken $token): RedirectResponse
    {
        abort_if($token->tokenable_id !== $request->user()->id, 403);

        $token->delete();

        return redirect()->route('account.show');
    }
}
