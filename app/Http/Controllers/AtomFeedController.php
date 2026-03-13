<?php

namespace App\Http\Controllers;

use App\Models\Moment;
use App\Models\User;
use Illuminate\Http\Response;

class AtomFeedController extends Controller
{
    public function __invoke(): Response
    {
        $moments = Moment::query()
            ->with(['user', 'images'])
            ->latest()
            ->limit(20)
            ->get();

        $user = User::first();

        return response()
            ->view('feed-atom', ['moments' => $moments, 'user' => $user])
            ->header('Content-Type', 'application/atom+xml; charset=UTF-8');
    }
}
