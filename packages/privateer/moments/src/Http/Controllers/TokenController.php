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
        $validated = $request->validated();
        $abilities = match ($validated['role']) {
            'read-only' => ['moments:read'],
            'read-write' => ['moments:read', 'moments:write'],
        };
        $token = $request->user()->createToken($validated['name'], $abilities);

        return redirect()->route('account.show')
            ->with('plain_text_token', $token->plainTextToken);
    }

    public function destroy(Request $request, PersonalAccessToken $token): RedirectResponse
    {
        abort_if((int) $token->tokenable_id !== (int) $request->user()->id, 403);

        $token->delete();

        return redirect()->route('account.show');
    }
}
