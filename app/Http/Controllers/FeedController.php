<?php

namespace App\Http\Controllers;

use App\Models\Moment;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    public function __invoke(): Response
    {
        $moments = Moment::query()
            ->with(['user', 'images'])
            ->latest()
            ->limit(20)
            ->get();

        return response()
            ->view('feed', ['moments' => $moments])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
