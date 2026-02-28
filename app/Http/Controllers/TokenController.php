<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTokenRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Sanctum\PersonalAccessToken;

class TokenController extends Controller
{
    public function index(Request $request): View
    {
        $tokens = $request->user()->tokens()->latest()->get();

        return view('tokens.index', ['tokens' => $tokens]);
    }

    public function store(StoreTokenRequest $request): RedirectResponse
    {
        $token = $request->user()->createToken($request->validated()['name']);

        return redirect()->route('tokens.index')
            ->with('plain_text_token', $token->plainTextToken);
    }

    public function destroy(Request $request, PersonalAccessToken $token): RedirectResponse
    {
        abort_if($token->tokenable_id !== $request->user()->id, 403);
        $token->delete();

        return redirect()->route('tokens.index');
    }
}
