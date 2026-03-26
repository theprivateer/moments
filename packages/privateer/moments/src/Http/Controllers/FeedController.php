<?php

namespace Privateer\Moments\Http\Controllers;

use Illuminate\Http\Response;
use Privateer\Moments\Support\Moments as MomentsSupport;

class FeedController extends Controller
{
    public function __invoke(): Response
    {
        $momentModel = MomentsSupport::momentModel();

        $moments = $momentModel::query()
            ->with(['user', 'images'])
            ->latest()
            ->limit(20)
            ->get();

        return response()
            ->view('moments::feed', ['moments' => $moments])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
