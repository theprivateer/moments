<?php

namespace Privateer\Moments\Http\Controllers;

use Illuminate\Http\Response;
use Privateer\Moments\Support\Moments as MomentsSupport;

class AtomFeedController extends Controller
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
            ->view('moments::feed-atom', [
                'moments' => $moments,
                'user' => MomentsSupport::firstUser(),
            ])
            ->header('Content-Type', 'application/atom+xml; charset=UTF-8');
    }
}
